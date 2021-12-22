Nova.booting((Vue, router) => {
    Vue.component(
        "index-fontawesome",
        require("./components/IndexField").default
    );
    Vue.component(
        "detail-fontawesome",
        require("./components/DetailField").default
    );
    Vue.component(
        "form-fontawesome",
        require("./components/FormField").default
    );
});
