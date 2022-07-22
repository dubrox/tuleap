/*
 * Copyright (c) Enalean, 2022-Present. All Rights Reserved.
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

import { vite, viteDtsPlugin } from "@tuleap/build-system-configurator";
import * as path from "path";
import PoGettextPlugin from "@tuleap/po-gettext-plugin";
import pkg from "./package.json";

export default vite.defineLibConfig({
    plugins: [PoGettextPlugin.vite(), viteDtsPlugin()],
    build: {
        lib: {
            entry: path.resolve(__dirname, "src/main.ts"),
            name: "ChartBuilder",
        },
        rollupOptions: {
            external: Object.keys(pkg.dependencies),
            output: {
                globals: {
                    "d3-array": "d3Array",
                    "d3-axis": "d3Axis",
                    "d3-scale": "d3Scale",
                    "d3-selection": "d3Selection",
                    "d3-shape": "d3Shape",
                    moment: "moment",
                    "sprintf-js": "sprintfJs",
                    "@tuleap/gettext": "TuleapGettext",
                },
            },
        },
    },
});
