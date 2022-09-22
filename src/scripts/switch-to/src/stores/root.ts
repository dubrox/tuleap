/*
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
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

import type { State } from "./type";
import { defineStore } from "pinia";
import { get } from "@tuleap/tlp-fetch";
import type { Project, UserHistory, ItemDefinition } from "../type";
import { isMatchingFilterValue } from "../helpers/is-matching-filter-value";
import { useFullTextStore } from "./fulltext";

export const useRootStore = defineStore("root", {
    state: (): State => ({
        projects: [],
        is_trove_cat_enabled: false,
        are_restricted_users_allowed: false,
        is_search_available: false,
        search_form: {
            type_of_search: "",
            hidden_fields: [],
        },
        user_id: 100,
        is_loading_history: false,
        is_history_loaded: false,
        is_history_in_error: false,
        history: { entries: [] },
        filter_value: "",
    }),
    getters: {
        filtered_history(): UserHistory {
            return {
                entries: this.history.entries.reduce(
                    (
                        matching_entries: ItemDefinition[],
                        entry: ItemDefinition
                    ): ItemDefinition[] => {
                        if (isMatchingFilterValue(entry.title, this.keywords)) {
                            matching_entries.push(entry);
                        } else if (isMatchingFilterValue(entry.xref, this.keywords)) {
                            matching_entries.push(entry);
                        }

                        return matching_entries;
                    },
                    []
                ),
            };
        },

        filtered_projects(): Project[] {
            return this.projects.reduce(
                (matching_projects: Project[], project: Project): Project[] => {
                    if (isMatchingFilterValue(project.project_name, this.keywords)) {
                        matching_projects.push(project);
                    }

                    return matching_projects;
                },
                []
            );
        },

        keywords(): string {
            return this.filter_value.trim();
        },

        is_in_search_mode(): boolean {
            return this.keywords.length > 0;
        },
    },
    actions: {
        async loadHistory(): Promise<void> {
            if (this.is_history_loaded) {
                return;
            }

            try {
                const response = await get(`/api/users/${this.user_id}/history`);
                const history: UserHistory = await response.json();
                this.saveHistory(history);
            } catch (e) {
                this.setErrorForHistory(true);
                throw e;
            }
        },

        updateFilterValue(value: string): void {
            if (this.filter_value !== value) {
                this.filter_value = value;

                if (this.is_in_search_mode) {
                    useFullTextStore().search(this.keywords);
                }
            }
        },

        saveHistory(history: UserHistory): void {
            this.is_history_loaded = true;
            this.is_loading_history = false;
            this.history = history;
        },

        setErrorForHistory(is_error: boolean): void {
            this.is_history_in_error = is_error;
        },
    },
});