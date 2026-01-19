/**
 * Client-side fuzzy search utility for Font Awesome icons.
 * Provides instant results for common icons when API is slow or unavailable.
 */

// Common icons that are frequently used
const commonIcons = [
    { id: "user", label: "User", keywords: ["person", "account", "profile"] },
    { id: "home", label: "Home", keywords: ["house", "main", "start"] },
    { id: "search", label: "Search", keywords: ["find", "magnify", "look"] },
    { id: "heart", label: "Heart", keywords: ["love", "like", "favorite"] },
    { id: "star", label: "Star", keywords: ["favorite", "rate", "rating"] },
    {
        id: "envelope",
        label: "Envelope",
        keywords: ["email", "mail", "message"],
    },
    { id: "phone", label: "Phone", keywords: ["call", "telephone", "contact"] },
    { id: "camera", label: "Camera", keywords: ["photo", "picture", "image"] },
    {
        id: "calendar",
        label: "Calendar",
        keywords: ["date", "schedule", "event"],
    },
    { id: "clock", label: "Clock", keywords: ["time", "watch", "hour"] },
    { id: "download", label: "Download", keywords: ["save", "get", "arrow"] },
    { id: "upload", label: "Upload", keywords: ["send", "share", "arrow"] },
    { id: "edit", label: "Edit", keywords: ["pen", "pencil", "modify"] },
    { id: "trash", label: "Trash", keywords: ["delete", "remove", "bin"] },
    { id: "save", label: "Save", keywords: ["floppy", "disk", "store"] },
    { id: "copy", label: "Copy", keywords: ["duplicate", "clone", "files"] },
    { id: "link", label: "Link", keywords: ["chain", "url", "connect"] },
    { id: "image", label: "Image", keywords: ["picture", "photo", "media"] },
    { id: "video", label: "Video", keywords: ["movie", "film", "media"] },
    { id: "music", label: "Music", keywords: ["audio", "sound", "note"] },
    { id: "play", label: "Play", keywords: ["start", "video", "media"] },
    { id: "pause", label: "Pause", keywords: ["stop", "wait", "media"] },
    { id: "stop", label: "Stop", keywords: ["end", "halt", "media"] },
    { id: "lock", label: "Lock", keywords: ["secure", "password", "closed"] },
    { id: "unlock", label: "Unlock", keywords: ["open", "access", "unsecure"] },
    { id: "key", label: "Key", keywords: ["password", "access", "security"] },
    {
        id: "shield",
        label: "Shield",
        keywords: ["protect", "security", "guard"],
    },
    {
        id: "exclamation-triangle",
        label: "Warning",
        keywords: ["alert", "danger", "caution"],
    },
    {
        id: "info-circle",
        label: "Info",
        keywords: ["information", "help", "about"],
    },
    {
        id: "question-circle",
        label: "Question",
        keywords: ["help", "faq", "support"],
    },
    { id: "check", label: "Check", keywords: ["done", "complete", "yes", "ok"] },
    { id: "times", label: "Times", keywords: ["close", "x", "cancel", "no"] },
    { id: "plus", label: "Plus", keywords: ["add", "new", "create"] },
    { id: "minus", label: "Minus", keywords: ["remove", "subtract", "less"] },
    { id: "cog", label: "Settings", keywords: ["gear", "config", "options"] },
    {
        id: "bell",
        label: "Bell",
        keywords: ["notification", "alert", "alarm"],
    },
    { id: "bookmark", label: "Bookmark", keywords: ["save", "favorite", "mark"] },
    {
        id: "folder",
        label: "Folder",
        keywords: ["directory", "files", "organize"],
    },
    { id: "file", label: "File", keywords: ["document", "page", "paper"] },
    { id: "database", label: "Database", keywords: ["data", "storage", "server"] },
    {
        id: "arrow-right",
        label: "Arrow Right",
        keywords: ["next", "forward", "direction"],
    },
    {
        id: "arrow-left",
        label: "Arrow Left",
        keywords: ["back", "previous", "direction"],
    },
    {
        id: "arrow-up",
        label: "Arrow Up",
        keywords: ["up", "top", "direction"],
    },
    {
        id: "arrow-down",
        label: "Arrow Down",
        keywords: ["down", "bottom", "direction"],
    },
    {
        id: "chevron-right",
        label: "Chevron Right",
        keywords: ["next", "expand", "arrow"],
    },
    {
        id: "chevron-left",
        label: "Chevron Left",
        keywords: ["back", "collapse", "arrow"],
    },
    {
        id: "chevron-up",
        label: "Chevron Up",
        keywords: ["up", "collapse", "arrow"],
    },
    {
        id: "chevron-down",
        label: "Chevron Down",
        keywords: ["down", "expand", "arrow"],
    },
    { id: "bars", label: "Bars", keywords: ["menu", "hamburger", "nav"] },
    {
        id: "ellipsis",
        label: "Ellipsis",
        keywords: ["more", "options", "dots"],
    },
    {
        id: "spinner",
        label: "Spinner",
        keywords: ["loading", "wait", "progress"],
    },
    {
        id: "circle-notch",
        label: "Circle Notch",
        keywords: ["loading", "spinner", "wait"],
    },
    { id: "sync", label: "Sync", keywords: ["refresh", "reload", "update"] },
    {
        id: "redo",
        label: "Redo",
        keywords: ["repeat", "again", "forward"],
    },
    { id: "undo", label: "Undo", keywords: ["back", "revert", "previous"] },
    {
        id: "share",
        label: "Share",
        keywords: ["social", "send", "network"],
    },
    { id: "print", label: "Print", keywords: ["printer", "paper", "document"] },
    { id: "filter", label: "Filter", keywords: ["sort", "funnel", "search"] },
    { id: "sort", label: "Sort", keywords: ["order", "arrange", "filter"] },
    { id: "list", label: "List", keywords: ["items", "menu", "bullets"] },
    { id: "th", label: "Grid", keywords: ["table", "cells", "layout"] },
    { id: "table", label: "Table", keywords: ["grid", "data", "cells"] },
    { id: "chart-bar", label: "Chart Bar", keywords: ["graph", "stats", "data"] },
    {
        id: "chart-line",
        label: "Chart Line",
        keywords: ["graph", "stats", "data"],
    },
    {
        id: "chart-pie",
        label: "Chart Pie",
        keywords: ["graph", "stats", "data"],
    },
    { id: "globe", label: "Globe", keywords: ["world", "earth", "internet"] },
    { id: "map", label: "Map", keywords: ["location", "place", "geography"] },
    {
        id: "map-marker",
        label: "Map Marker",
        keywords: ["location", "pin", "place"],
    },
    {
        id: "location-dot",
        label: "Location",
        keywords: ["pin", "place", "marker"],
    },
    { id: "tag", label: "Tag", keywords: ["label", "category", "price"] },
    { id: "tags", label: "Tags", keywords: ["labels", "categories", "prices"] },
    { id: "comment", label: "Comment", keywords: ["message", "chat", "bubble"] },
    {
        id: "comments",
        label: "Comments",
        keywords: ["messages", "chat", "discussion"],
    },
    { id: "users", label: "Users", keywords: ["people", "group", "team"] },
    {
        id: "user-plus",
        label: "User Plus",
        keywords: ["add", "new", "person"],
    },
    {
        id: "user-minus",
        label: "User Minus",
        keywords: ["remove", "delete", "person"],
    },
    { id: "eye", label: "Eye", keywords: ["view", "see", "visible"] },
    {
        id: "eye-slash",
        label: "Eye Slash",
        keywords: ["hide", "invisible", "hidden"],
    },
    {
        id: "thumbs-up",
        label: "Thumbs Up",
        keywords: ["like", "approve", "good"],
    },
    {
        id: "thumbs-down",
        label: "Thumbs Down",
        keywords: ["dislike", "reject", "bad"],
    },
    { id: "flag", label: "Flag", keywords: ["report", "mark", "country"] },
    { id: "ban", label: "Ban", keywords: ["block", "forbidden", "prohibited"] },
    {
        id: "check-circle",
        label: "Check Circle",
        keywords: ["done", "complete", "success"],
    },
    {
        id: "times-circle",
        label: "Times Circle",
        keywords: ["close", "error", "cancel"],
    },
    {
        id: "exclamation-circle",
        label: "Exclamation Circle",
        keywords: ["alert", "warning", "attention"],
    },
];

/**
 * Calculate Levenshtein distance between two strings.
 */
function levenshteinDistance(str1, str2) {
    const m = str1.length;
    const n = str2.length;

    if (m === 0) return n;
    if (n === 0) return m;

    const matrix = [];

    for (let i = 0; i <= m; i++) {
        matrix[i] = [i];
    }

    for (let j = 0; j <= n; j++) {
        matrix[0][j] = j;
    }

    for (let i = 1; i <= m; i++) {
        for (let j = 1; j <= n; j++) {
            if (str1[i - 1] === str2[j - 1]) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1, // substitution
                    matrix[i][j - 1] + 1, // insertion
                    matrix[i - 1][j] + 1 // deletion
                );
            }
        }
    }

    return matrix[m][n];
}

/**
 * Calculate similarity score between two strings (0-1, higher = more similar).
 */
function similarityScore(str1, str2) {
    const s1 = str1.toLowerCase();
    const s2 = str2.toLowerCase();

    // Exact match
    if (s1 === s2) return 1;

    // Contains match
    if (s2.includes(s1)) return 0.9;
    if (s1.includes(s2)) return 0.85;

    // Starts with match
    if (s2.startsWith(s1)) return 0.8;

    // Levenshtein-based similarity
    const distance = levenshteinDistance(s1, s2);
    const maxLength = Math.max(s1.length, s2.length);
    return Math.max(0, 1 - distance / maxLength);
}

/**
 * Search icons using fuzzy matching.
 * @param {string} query - Search query
 * @param {Object} options - Search options
 * @param {number} options.threshold - Minimum similarity score (0-1)
 * @param {number} options.maxResults - Maximum number of results
 * @returns {Array} Matched icons with scores
 */
export function fuzzySearchIcons(query, options = {}) {
    const { threshold = 0.3, maxResults = 50 } = options;

    if (!query || query.trim().length === 0) {
        return commonIcons.slice(0, maxResults);
    }

    const searchQuery = query.toLowerCase().trim();
    const results = [];

    for (const icon of commonIcons) {
        // Calculate scores for id, label, and keywords
        const idScore = similarityScore(searchQuery, icon.id);
        const labelScore = similarityScore(searchQuery, icon.label);
        const keywordScores = icon.keywords.map((kw) =>
            similarityScore(searchQuery, kw)
        );
        const maxKeywordScore = Math.max(0, ...keywordScores);

        // Use the best score
        const bestScore = Math.max(idScore, labelScore, maxKeywordScore);

        if (bestScore >= threshold) {
            results.push({
                ...icon,
                score: bestScore,
                familyStylesByLicense: {
                    free: [
                        { family: "classic", style: "solid" },
                        { family: "classic", style: "regular" },
                    ],
                    pro: [],
                },
                svgs: [],
                _fuzzyMatch: true,
            });
        }
    }

    // Sort by score (descending) and limit results
    return results
        .sort((a, b) => b.score - a.score)
        .slice(0, maxResults);
}

/**
 * Get all common icons (for initial display).
 */
export function getCommonIcons(maxResults = 50) {
    return commonIcons.slice(0, maxResults).map((icon) => ({
        ...icon,
        familyStylesByLicense: {
            free: [
                { family: "classic", style: "solid" },
                { family: "classic", style: "regular" },
            ],
            pro: [],
        },
        svgs: [],
        _fuzzyMatch: true,
    }));
}

export default {
    fuzzySearchIcons,
    getCommonIcons,
    commonIcons,
};
