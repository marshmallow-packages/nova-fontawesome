<template>
    <Modal
        :show="true"
        @confirm="handleConfirm"
        @close="handleClose"
        class="fontawesome-modal bg-white modal border bg-white dark:bg-gray-800 rounded-lg shadow-lg border-gray overflow-hidden"
    >
        <ModalHeader class="px-6 py-6 border-b relative border-gray">
            {{ __("Select Icon") }}

            <a href="#" class="fontawesome-close" @click.prevent="handleClose">
                <i class="fa fa-times"></i>
            </a>
        </ModalHeader>
        <div class="px-2 py-4 rounded-lg bg-white">
            <div class="flex flex-wrap">
                <div class="w-1/2 px-4">
                    <SelectControl
                        class="w-full form-control form-select"
                        :placeholder="__('All')"
                        v-model="filter.type"
                    >
                        <option value disabled="disabled">Select a type</option>
                        <option value="all">All</option>
                        <option
                            v-for="def in definitions"
                            :key="def"
                            :value="stringToDefinition(def)"
                            v-html="def"
                        ></option>
                    </SelectControl>
                </div>
                <div class="w-1/2 px-4">
                    <input
                        type="text"
                        id="search"
                        class="w-full form-control form-input form-input-bordered"
                        placeholder="Search icons"
                        v-model="filter.search"
                    />
                </div>
            </div>
            <div class="px-6 py-6 fontawesome-inner">
                <div v-if="isLoading">{{ __("Loading") }}...</div>
                <div
                    class="flex flex-wrap items-stretch -mx-2"
                    v-else-if="icons.length > 0 && !isLoading"
                >
                    <div
                        v-for="(icon, index) in showable_icons"
                        :key="index"
                        class="inner flex items-center justify-center text-center px-2 icon-box cursor-pointer"
                        @click="saveIcon(icon)"
                    >
                        <div
                            :data-class="icon.prefix + ' fa-' + icon.iconName"
                            class="p-4"
                        >
                            <i
                                :class="icon.prefix + ' fa-' + icon.iconName"
                            ></i>
                            <span
                                class="icon-name"
                                v-html="icon.iconName"
                            ></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ModalFooter class="flex justify-end">
            <div class="ml-auto">
                <CancelButton
                    component="button"
                    type="button"
                    dusk="cancel-action-button"
                    @click.prevent="handleClose"
                />

                <LoadingButton
                    class="ml-3"
                    type="submit"
                    ref="runButton"
                    component="DefaultButton"
                    :disabled="isLoading"
                    :loading="isLoading"
                    @click="handleConfirm"
                >
                    {{ __("Save") }}
                </LoadingButton>
            </div>
        </ModalFooter>
    </Modal>
</template>

<script>
    import { FormField, HandlesValidationErrors } from "laravel-nova";

    export default {
        name: "GeneralModal",
        mixins: [FormField, HandlesValidationErrors],

        props: ["field"],
        data: () => ({
            isLoading: false,
            modalOpen: false,
            library: {},
            icons: [],
            showable_icons: [],
            value: "",
            definitions: [],
            defaultIconObj: {},
            filter: {
                type: "",
                search: "",
            },
        }),
        beforeMount() {
            this.loadIcons();
        },
        mounted() {
            this.icons.sort((a, b) =>
                a.iconName > b.iconName ? 1 : b.iconName > a.iconName ? -1 : 0
            );

            // Set default icon object
            if (this.defaultIcon && this.defaultIconType) {
                let i = this.icons.filter(
                    (icon) =>
                        icon.prefix === this.defaultIconType &&
                        icon.iconName === this.defaultIcon
                );

                if (i[0]) {
                    this.defaultIconObj = i[0];
                }
            }
        },

        methods: {
            async loadIcons() {
                let arr = {};
                this.isLoading = true;

                const fab = require("../../icons/fab.json");
                arr.fab = fab;

                if (this.pro) {
                    const fas = require("../../icons/fas_pro.json");
                    const far = require("../../icons/far_pro.json");
                    const fal = require("../../icons/fal_pro.json");
                    const fad = require("../../icons/fad_pro.json");
                    const fat = require("../../icons/fat_pro.json");

                    arr.far = far;
                    arr.fas = fas;
                    arr.fal = fal;
                    arr.fad = fad;
                    arr.fat = fat;
                } else {
                    const fas = require("../../icons/fas.json");
                    const far = require("../../icons/far.json");

                    arr.far = far;
                    arr.fas = fas;
                }

                let icons = [];
                for (let key in arr) {
                    this.definitions.push(this.definitionToString(key));

                    for (let i in arr[key]) {
                        let icon = arr[key][i];

                        if (this.canShowIcon(icon)) {
                            icon.show = true;
                            icons.push(icon);
                        }
                    }
                }
                this.isLoading = false;
                this.icons = icons;
                this.showable_icons = icons;
            },
            displayIcon(icon, filter) {
                return (
                    (filter.type == "" ||
                        filter.type == "all" ||
                        filter.type == icon.prefix) &&
                    icon.show
                );
            },
            canShowIcon(icon) {
                if (typeof this.field.only !== "undefined") {
                    if (this.field.only.indexOf(icon.iconName) === -1) {
                        return false;
                    }
                }

                return !icon.iconName ||
                    !icon.prefix ||
                    icon.iconName === "font-awesome-logo-full"
                    ? false
                    : true;
            },

            clear() {
                if (
                    this.enforceDefaultIcon &&
                    this.defaultIcon &&
                    this.defaultIconType &&
                    this.defaultIconObj.iconName
                ) {
                    this.value = this.defaultIconOutput;
                    this.saveIcon(this.defaultIconObj);
                } else {
                    this.value = "";
                }

                this.clearFilter();
            },

            clearFilter() {
                this.filter.type = "";
                this.filter.search = "";
            },

            closeModal() {
                this.modalOpen = false;
                this.clearFilter();
            },

            toggleModal() {
                this.modalOpen = !this.modalOpen;
                this.clearFilter();
            },

            saveIcon(icon) {
                console.log(icon);
                // if (this.$el.getElementsByClassName("js-icon").length > 0) {
                //     this.$el
                //         .getElementsByClassName("js-icon")[0]
                //         .setAttribute(
                //             "class",
                //             "js-icon " + icon.prefix + " fa-" + icon.iconName
                //         );
                // }

                this.value = icon.prefix + " fa-" + icon.iconName;

                console.log(this.value);
                this.filter.type = "";
                this.filter.search = "";

                this.closeModal();
            },

            /*
             * Convert the class to string
             */
            definitionToString(def) {
                switch (def) {
                    case "far":
                        return "Regular";
                        break;
                    case "fas":
                        return "Solid";
                        break;
                    case "fab":
                        return "Brands";
                        break;
                    case "fal":
                        return "Light";
                        break;
                    case "fad":
                        return "Duotone";
                        break;
                    case "fat":
                        return "Thin";
                        break;
                }
            },

            /*
             * Convert the string to class method
             */
            stringToDefinition(str) {
                switch (str) {
                    case "Regular":
                        return "far";
                        break;
                    case "Solid":
                        return "fas";
                        break;
                    case "Brands":
                        return "fab";
                        break;
                    case "Light":
                        return "fal";
                        break;
                    case "Duotone":
                        return "fad";
                        break;
                    case "Thin":
                        return "fat";
                        break;
                }
            },

            /*
             * Set the initial, internal value for the field.
             */
            setInitialValue() {
                this.value = this.field.value || this.defaultIconOutput;
            },

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                formData.append(
                    this.field.attribute,
                    this.value || this.defaultIconOutput
                );
            },

            /**
             * Update the field's internal value.
             */
            handleChange(value) {
                this.value = value;
            },

            search() {
                let keyword = this.filter.search.toUpperCase();

                for (let i in this.icons) {
                    if (keyword == "") {
                        this.icons[i].show = true;
                    } else {
                        let alt = keyword.replace("-", " ");
                        let name = this.icons[i].iconName.toUpperCase();
                        let nameAlt = name.replace("-", " ");

                        if (
                            name.includes(keyword) ||
                            name.indexOf(keyword) !== -1 ||
                            nameAlt.includes(alt) ||
                            nameAlt.indexOf(alt) !== -1
                        ) {
                            this.showable_icons[i].show = true;
                        } else {
                            this.showable_icons[i].show = false;
                        }
                    }
                }

                this.$nextTick(function () {
                    this.icons = this.showable_icons;
                    this.isLoading = false;
                });
            },

            handleClose() {
                this.$emit("close");
            },
            handleConfirm() {
                this.$emit("confirm");
            },
        },
        computed: {
            pro() {
                return this.field.pro || false;
            },
            defaultIcon() {
                return this.field.default_icon || "";
            },
            defaultIconType() {
                return this.field.default_icon_type || "";
            },
            addButtonText() {
                return this.field.add_button_text || "Add Icon";
            },
            enforceDefaultIcon() {
                return this.field.enforce_default_icon || false;
            },
            defaultIconOutput() {
                return this.defaultIconType + " fa-" + this.defaultIcon;
            },
        },

        watch: {
            "filter.search": {
                handler(val) {
                    this.isLoading = true;
                    this.search();
                },
            },
            "filter.type": {
                handler(val) {
                    console.log(val);
                    console.log(this.filter);
                    this.isLoading = true;

                    this.$nextTick(function () {
                        this.isLoading = false;
                    });
                },
            },
        },
    };
</script>

<style scoped></style>
