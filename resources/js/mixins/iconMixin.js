// Shorthand prefix mappings
const shorthandMap = {
    fas: { family: "classic", style: "solid" },
    far: { family: "classic", style: "regular" },
    fal: { family: "classic", style: "light" },
    fat: { family: "classic", style: "thin" },
    fad: { family: "duotone", style: "solid" },
    fab: { family: "brands", style: "brands" },
    fass: { family: "sharp", style: "solid" },
    fasr: { family: "sharp", style: "regular" },
    fasl: { family: "sharp", style: "light" },
    fast: { family: "sharp", style: "thin" },
    fasds: { family: "sharp-duotone", style: "solid" },
};

// Style class mappings
const styleMap = {
    "fa-solid": "solid",
    "fa-regular": "regular",
    "fa-light": "light",
    "fa-thin": "thin",
    "fa-brands": "brands",
};

// Family class mappings
const familyMap = {
    "fa-sharp": "sharp",
    "fa-duotone": "duotone",
    "fa-sharp-duotone": "sharp-duotone",
};

export const iconMixin = {
    methods: {
        /**
         * Parse Font Awesome class string into family, style, and icon name.
         */
        parseFontAwesomeClasses(classString) {
            const classes = classString
                .toLowerCase()
                .split(" ")
                .map((c) => c.trim());

            let family = null;
            let style = null;

            for (const cls of classes) {
                // Check shorthand prefixes first
                if (shorthandMap[cls]) {
                    family = shorthandMap[cls].family;
                    style = shorthandMap[cls].style;
                    continue;
                }

                // Check family modifiers
                if (familyMap[cls]) {
                    family = familyMap[cls];
                    continue;
                }

                // Check style classes
                if (styleMap[cls]) {
                    style = styleMap[cls];
                    // Brands is both family and style
                    if (cls === "fa-brands") {
                        family = "brands";
                    }
                }
            }

            // Extract icon name
            const iconClass = classes.find(
                (c) => c.startsWith("fa-") && !styleMap[c] && !familyMap[c]
            );
            const icon = iconClass ? iconClass.replace("fa-", "") : null;

            return { faFamily: family, faStyle: style, faIcon: icon };
        },

        /**
         * Infer the family from a style.
         * Most styles default to 'classic', except 'brands' which implies 'brands' family.
         */
        inferFamilyFromStyle(style) {
            if (!style) return "classic";

            if (style.toLowerCase() === "brands") {
                return "brands";
            }

            return "classic";
        },

        /**
         * Infer the default style from a family.
         * 'brands' family only has 'brands' style, all others default to 'solid'.
         */
        inferStyleFromFamily(family) {
            if (!family) return "solid";

            if (family.toLowerCase() === "brands") {
                return "brands";
            }

            return "solid";
        },

        /**
         * Get resolved family and style, filling in defaults where needed.
         */
        getResolvedFamilyAndStyle(family, style) {
            // If neither provided, use defaults
            if (!family && !style) {
                return { family: "classic", style: "solid" };
            }

            // If only style provided, infer family
            if (!family && style) {
                return { family: this.inferFamilyFromStyle(style), style };
            }

            // If only family provided, infer style
            if (family && !style) {
                return { family, style: this.inferStyleFromFamily(family) };
            }

            return { family, style };
        },

        getIconSvg(icon, preferredFamily = null, preferredStyle = null) {
            if (!icon.svgs || icon.svgs.length === 0) {
                return null;
            }

            // If we have preferred family/style, try to find that first
            if (preferredFamily && preferredStyle) {
                const exactMatch = icon.svgs.find(
                    (s) =>
                        s.familyStyle?.family?.toLowerCase() ===
                            preferredFamily.toLowerCase() &&
                        s.familyStyle?.style?.toLowerCase() ===
                            preferredStyle.toLowerCase()
                );
                if (exactMatch && exactMatch.pathData) {
                    return { svg: this.buildSvgFromPath(exactMatch), icon };
                }
            }

            // Fallback: prefer solid, then regular, then first available
            const preferredOrder = [
                "solid",
                "regular",
                "brands",
                "light",
                "thin",
                "duotone",
            ];

            for (const preferred of preferredOrder) {
                const svgData = icon.svgs.find(
                    (s) => s.familyStyle?.style?.toLowerCase() === preferred
                );
                if (svgData && svgData.pathData) {
                    return { svg: this.buildSvgFromPath(svgData), icon };
                }
            }

            // Fallback to first available
            if (icon.svgs[0] && icon.svgs[0].pathData) {
                const svgData = icon.svgs[0];
                return { svg: this.buildSvgFromPath(svgData), icon };
            }

            return null;
        },

        buildSvgFromPath(svgData) {
            const style = svgData.familyStyle?.style;
            const family = svgData.familyStyle?.family;
            const pathData = svgData.pathData;
            const width = svgData.width || 512;
            const height = svgData.height || 512;

            if (!pathData || pathData.length === 0) {
                return `<svg viewBox="0 0 ${width} ${height}"></svg>`;
            }

            // pathData is an array
            // For monotone: only one path (index 0)
            // For duotone: two paths - index 0 is secondary, index 1 is primary
            const isDuotone =
                (family?.toLowerCase() === "duotone" ||
                    family?.toLowerCase() === "sharp-duotone") &&
                pathData.length === 2;

            let paths = "";
            if (isDuotone) {
                // Secondary path (lighter)
                if (pathData[0]) {
                    paths += `<path d="${pathData[0]}" opacity="0.4"/>`;
                }
                // Primary path
                if (pathData[1]) {
                    paths += `<path d="${pathData[1]}"/>`;
                }
            } else {
                // Monotone icon - single path
                if (pathData[0]) {
                    paths = `<path d="${pathData[0]}"/>`;
                }
            }

            return `<svg viewBox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg">${paths}</svg>`;
        },
    },
};
