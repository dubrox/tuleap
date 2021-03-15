/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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
import type { HandleDragPayload } from "../type";
import type { SuccessfulDropCallbackParameter } from "@tuleap/drag-and-drop";
import { planElementInProgramIncrement } from "./ProgramIncrement/Feature/feature-planner";
import { addElementToTopBackLog } from "./ProgramIncrement/add-to-top-backlog";

export interface FeatureToPlan {
    id: number;
}

export function isContainer(element: HTMLElement): boolean {
    return Boolean(element.dataset.isContainer);
}

export function canMove(element: HTMLElement): boolean {
    return element.draggable;
}

export function invalid(handle: HTMLElement): boolean {
    return Boolean(handle.closest("[data-not-drag-handle]"));
}

export function isConsideredInDropzone(child: Element): boolean {
    return child.hasAttribute("draggable");
}

export function checkAcceptsDrop(payload: HandleDragPayload): boolean {
    if (
        !(payload.dropped_card instanceof HTMLElement) ||
        !(payload.target_cell instanceof HTMLElement) ||
        !(payload.source_cell instanceof HTMLElement)
    ) {
        return false;
    }

    const user_can_plan = Boolean(payload.target_cell.dataset.canPlan);
    if (!user_can_plan) {
        const can_not_drop_message = payload.target_cell.getElementsByClassName(
            "drop-not-accepted-overlay"
        );

        if (!can_not_drop_message || !can_not_drop_message[0]) {
            return user_can_plan;
        }

        can_not_drop_message[0].classList.remove("drop-accepted");
        can_not_drop_message[0].classList.add("drop-not-accepted");
    }

    return user_can_plan;
}

export function checkAfterDrag(): void {
    const error_messages = document.getElementsByClassName("drop-not-accepted-overlay");

    [].forEach.call(error_messages, function (dom_message: HTMLElement) {
        dom_message.classList.remove("drop-not-accepted");
        dom_message.classList.add("drop-accepted");
    });
}

export async function handleDrop(
    context: SuccessfulDropCallbackParameter,
    program_id: number,
    location: Location
): Promise<void> {
    const element_id = context.dropped_element.dataset.elementId;
    if (!element_id) {
        return;
    }

    const plan_in_program_increment_id = context.target_dropzone.dataset.programIncrementId;

    if (plan_in_program_increment_id) {
        const feature_artifact_link_field_id = context.target_dropzone.dataset.artifactLinkFieldId;
        if (!feature_artifact_link_field_id) {
            return;
        }

        let features_id = context.target_dropzone.dataset.plannedFeatureIds;
        if (!features_id) {
            features_id = "";
        }

        const feature_to_plan = buildFeatureToPlan(features_id, parseInt(element_id, 10));
        await planElementInProgramIncrement(
            parseInt(plan_in_program_increment_id, 10),
            parseInt(feature_artifact_link_field_id, 10),
            feature_to_plan
        );

        location.reload();
    }

    const remove_from_program_increment_id = context.dropped_element.dataset.programIncrementId;
    if (remove_from_program_increment_id) {
        let features_id = context.dropped_element.dataset.plannedFeatureIds;
        if (!features_id) {
            features_id = "";
        }

        const feature_artifact_link_field_id = context.dropped_element.dataset.artifactLinkFieldId;
        if (!feature_artifact_link_field_id) {
            return;
        }

        const feature_to_unplan = buildFeatureToUnplan(features_id, parseInt(element_id, 10));
        await planElementInProgramIncrement(
            parseInt(remove_from_program_increment_id, 10),
            parseInt(feature_artifact_link_field_id, 10),
            feature_to_unplan
        );

        await addElementToTopBackLog(program_id, parseInt(element_id, 10));

        location.reload();
    }
}

function buildFeatureToUnplan(existing_features: string, element_id: number): Array<FeatureToPlan> {
    const feature_to_plan: Array<FeatureToPlan> = [];

    const feature_list = existing_features.split(",");
    feature_list.forEach((feature) => {
        const feature_id = parseInt(feature, 10);
        if (feature_id !== element_id) {
            feature_to_plan.push({ id: feature_id });
        }
    });
    return feature_to_plan;
}

function buildFeatureToPlan(existing_features: string, element_id: number): Array<FeatureToPlan> {
    const feature_to_plan: Array<FeatureToPlan> = [];
    feature_to_plan.push({ id: element_id });

    if (existing_features === "") {
        return feature_to_plan;
    }

    const feature_list = existing_features.split(",");
    feature_list.forEach((feature) => {
        feature_to_plan.push({ id: parseInt(feature, 10) });
    });
    return feature_to_plan;
}
