/**
 * Copyright (c) Enalean, 2021 - present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

import type {
    ExportDocument,
    Campaign,
    DateTimeLocaleInformation,
    ArtifactFieldValueStepDefinitionEnhancedWithResults,
    GenericGlobalExportProperties,
} from "../../../type";
import type { ArtifactResponse, TrackerStructure } from "@tuleap/plugin-docgen-docx";
import {
    formatArtifact,
    getArtifacts,
    getTestManagementExecution,
    memoize,
    retrieveArtifactsStructure,
    retrieveTrackerStructure,
} from "@tuleap/plugin-docgen-docx";
import { limitConcurrencyPool } from "@tuleap/concurrency-limit-pool";
import { getTraceabilityMatrix } from "./traceability-matrix-creator";
import { getExecutionsForCampaigns } from "./executions-for-campaigns-retriever";
import type { GettextProvider } from "@tuleap/gettext";
import { sprintf } from "sprintf-js";
import { buildStepDefinitionEnhancedWithResultsFunction } from "./step-test-definition-formatter";

interface TrackerStructurePromiseTuple {
    readonly tracker_id: number;
    readonly tracker_structure_promise: Promise<TrackerStructure>;
}

export async function createExportReport(
    gettext_provider: GettextProvider,
    global_properties: GenericGlobalExportProperties,
    campaign: Campaign,
    datetime_locale_information: DateTimeLocaleInformation
): Promise<ExportDocument<ArtifactFieldValueStepDefinitionEnhancedWithResults>> {
    const get_test_execution = memoize(getTestManagementExecution);

    const tracker_structure_promises_map: Map<number, TrackerStructurePromiseTuple> = new Map();
    if (global_properties.testdefinition_tracker_id !== null) {
        tracker_structure_promises_map.set(global_properties.testdefinition_tracker_id, {
            tracker_id: global_properties.testdefinition_tracker_id,
            tracker_structure_promise: retrieveTrackerStructure(
                global_properties.testdefinition_tracker_id
            ),
        });
    }

    const tracker_structure_map: Map<number, TrackerStructure> = new Map();
    await limitConcurrencyPool(
        4,
        [...tracker_structure_promises_map.values()],
        async (tracker_structure_tuple: TrackerStructurePromiseTuple): Promise<void> => {
            tracker_structure_map.set(
                tracker_structure_tuple.tracker_id,
                await tracker_structure_tuple.tracker_structure_promise
            );
        }
    );

    const executions_map = await getExecutionsForCampaigns([campaign]);
    const test_def_artifact_ids: Set<number> = new Set();
    if (global_properties.testdefinition_tracker_id !== null) {
        for (const { executions } of executions_map.values()) {
            for (const exec of executions) {
                test_def_artifact_ids.add(exec.definition.id);
            }
        }
    }

    const all_artifacts: ArtifactResponse[] = [
        ...(await getArtifacts(new Set(test_def_artifact_ids))).values(),
    ];
    const all_artifacts_structures = await retrieveArtifactsStructure(
        tracker_structure_map,
        all_artifacts,
        get_test_execution
    );

    return {
        name: sprintf(gettext_provider.gettext("Test campaign %(name)s"), { name: campaign.label }),
        traceability_matrix: getTraceabilityMatrix(executions_map, datetime_locale_information),
        backlog: [],
        tests: all_artifacts_structures
            .filter((artifact) => test_def_artifact_ids.has(artifact.id))
            .map((artifact) =>
                formatArtifact(
                    artifact,
                    datetime_locale_information,
                    global_properties.base_url,
                    global_properties.artifact_links_types,
                    buildStepDefinitionEnhancedWithResultsFunction(artifact, executions_map)
                )
            ),
    };
}
