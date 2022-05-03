import IndexField from "./components/IndexField.vue";
import DetailField from "./components/DetailField.vue";
import FormField from "./components/FormField.vue";

Nova.booting((Vue, router) => {
    Vue.component("index-nova-fontawesome", IndexField);
    Vue.component("detail-nova-fontawesome", DetailField);
    Vue.component("form-nova-fontawesome", FormField);
});
