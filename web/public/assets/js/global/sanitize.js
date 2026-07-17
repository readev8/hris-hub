var GlobalSanitize = GlobalSanitize || {};

GlobalSanitize.sanitize = function (str) {
    if (str == null) return "";
    if (typeof str !== "string") return String(str);
    var div = document.createElement("div");
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
};

GlobalSanitize.sanitizeHtml = function (str) {
    if (str == null) return "";
    var allowedTags = ["b", "i", "em", "strong", "u", "s", "strike", "a", "br", "p", "ul", "ol", "li"];
    var div = document.createElement("div");
    div.innerHTML = str;

    function cleanNode(node) {
        var child = node.firstChild;
        while (child) {
            var nextChild = child.nextSibling;
            if (child.nodeType === 1) {
                var tagName = child.tagName.toLowerCase();
                if (allowedTags.indexOf(tagName) === -1) {
                    while (child.firstChild) {
                        node.insertBefore(child.firstChild, child);
                    }
                    node.removeChild(child);
                } else {
                    var attrs = child.attributes;
                    for (var i = attrs.length - 1; i >= 0; i--) {
                        var attrName = attrs[i].name.toLowerCase();
                        if (attrName === "href" || attrName === "title") {
                            if (attrName === "href") {
                                var href = attrs[i].value;
                                if (href.indexOf("javascript:") === 0 || href.indexOf("data:") === 0) {
                                    child.removeAttribute(attrs[i].name);
                                }
                            }
                        } else {
                            child.removeAttribute(attrs[i].name);
                        }
                    }
                    cleanNode(child);
                }
            }
            child = nextChild;
        }
    }

    cleanNode(div);
    return div.innerHTML;
};

GlobalSanitize.escapeAttribute = function (str) {
    if (str == null) return "";
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#x27;");
};

window.GlobalSanitize = GlobalSanitize;
