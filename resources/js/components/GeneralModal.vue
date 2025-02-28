<template>
    <Modal
        :show="true"
        @confirm="handleConfirm"
        @close="handleClose"
        class="max-w-2xl flex flex-col h-full relative fontawesome-modal bg-white border bg-white dark:bg-gray-800 rounded-lg shadow-lg border-gray overflow-hidden"
    >
        <ModalHeader class="px-6 py-6 border-b relative border-gray">
            {{ __("novaFontawesome.modalTitle") }}

            <a href="#" class="fontawesome-close" @click.prevent="handleClose">
                <i class="fa fa-times"></i>
            </a>
        </ModalHeader>

        <div class="rounded-lg flex-1 relative h-90p bg-white">
            <div class="flex px-2 py-4 flex-wrap border-b border-gray">
                <div class="w-1/2 px-4">
                    <SelectControl
                        class="w-full"
                        :placeholder="__('All')"
                        v-model:selected="filter.type"
                        @change="filter.type = $event"
                    >
                        <option value disabled="disabled">
                            {{ __("novaFontawesome.selectType.default") }}
                        </option>
                        <option value="all">
                            {{ __("novaFontawesome.selectType.placeholder") }}
                        </option>
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
                        :placeholder="__('novaFontawesome.search.placeholder')"
                        v-model="filter.search"
                    />
                </div>
            </div>
            <div
                class="px-4 py-4 fontawesome-inner"
                @scroll="onScroll"
                id="iconContainer"
            >
                <div
                    class="py-6 text-center text-md font-semibold"
                    v-if="isLoading"
                >
                    {{ __("novaFontawesome.loading") }}...
                </div>
                <div
                    class="flex flex-wrap items-stretch -mx-2"
                    v-else-if="icons.length > 0 && !isLoading"
                >
                    <div
                        v-for="(icon, index) in chunkedIcons"
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
                >
                    {{ __("novaFontawesome.cancel") }}
                </CancelButton>

                <LoadingButton
                    class="ml-3"
                    type="submit"
                    ref="runButton"
                    component="DefaultButton"
                    :disabled="isLoading"
                    :loading="isLoading"
                    @click="handleConfirm"
                >
                    {{ __("novaFontawesome.save") }}
                </LoadingButton>
            </div>
        </ModalFooter>
    </Modal>
</template>

<script>
    import { FormField, HandlesValidationErrors } from "laravel-nova";

    import { library } from "@fortawesome/fontawesome-svg-core";
    import { fab } from "@fortawesome/free-brands-svg-icons";
    import { far } from "@fortawesome/free-regular-svg-icons";
    import { fas } from "@fortawesome/free-solid-svg-icons";
    import { far as far_pro } from "@fortawesome/pro-regular-svg-icons";
    import { fas as fas_pro } from "@fortawesome/pro-solid-svg-icons";

    import { fad } from "@fortawesome/pro-duotone-svg-icons";
    import { fal } from "@fortawesome/pro-light-svg-icons";
    import { fat } from "@fortawesome/pro-thin-svg-icons";

    export default {
        name: "GeneralModal",
        mixins: [FormField, HandlesValidationErrors],

        props: ["field"],
        data: () => ({
            isLoading: false,
            modalOpen: false,
            library: {},
            icons: [],
            iconsChunked: [],
            expanded: false,
            chunk: 0,
            value: "",
            definitions: [],
            defaultIconObj: {},
            iconTypes: {},
            iconContainer: null,
            filter: {
                type: "",
                search: "",
            },
            old_filter: {
                type: "",
                search: "",
            },
        }),
        async beforeMount() {
            this.isLoading = true;
            await this.loadIcons();
        },
        mounted() {
            this.filter.type = "all";
            this.old_filter.type = "all";

            if (this.icons.length > 0) {
                this.icons.sort((a, b) =>
                    a.iconName > b.iconName
                        ? 1
                        : b.iconName > a.iconName
                        ? -1
                        : 0
                );
            }

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

                library.add(fab);

                if (this.pro) {
                    library.add(fas_pro, far_pro, fad, fal, fat);
                } else {
                    library.add(fas, far);
                }

                arr = library.definitions;

                this.icon_types = arr;
                let icons = [];
                for (let key in arr) {
                    this.definitions.push(this.definitionToString(key));
                    for (let i in arr[key]) {
                        let iconName = i;
                        let iconData = arr[key][i];
                        let icon = {
                            prefix: key,
                            iconName: iconName,
                            iconData: iconData,
                        };

                        if (this.canShowIcon(icon)) {
                            icon.show = true;
                            icons.push(icon);
                        }
                    }
                }
                this.isLoading = false;
                this.icons = icons;

                return this.icons;
            },
            async getIcons() {
                this.isLoading = true;

                this.chunk = 0;
                this.iconsChunked = [];

                let icons = [];
                let all_types = this.icon_types;

                for (let key in all_types) {
                    let show = this.displayFilter(key);
                    if (show) {
                        for (let i in all_types[key]) {
                            let iconName = i;
                            let iconData = all_types[key][i];
                            let icon = {
                                prefix: key,
                                iconName: iconName,
                                iconData: iconData,
                            };

                            let show = this.displayIcon(icon);
                            if (show) {
                                icons.push(icon);
                            }
                        }
                    }
                }

                this.$nextTick(function () {
                    this.icons = icons;
                    this.isLoading = false;
                    this.getChunk();
                });

                this.iconContainer = document.querySelector("#iconContainer");
            },

            onScroll({ target: { scrollTop, clientHeight, scrollHeight } }) {
                if (
                    scrollTop + clientHeight >= scrollHeight - 250 &&
                    this.expanded === false
                ) {
                    this.expanded = true;
                    this.getChunk();
                }
            },
            getChunk() {
                let chunkSize = 100;

                let sortedIcons = this.icons.sort((a, b) => {
                    let fa = a.iconName.toLowerCase(),
                        fb = b.iconName.toLowerCase();
                    if (fa < fb) {
                        return -1;
                    }
                    if (fa > fb) {
                        return 1;
                    }
                    return 0;
                });

                let nextChunk = this.icons.slice(
                    this.chunk,
                    this.chunk + chunkSize
                );
                this.iconsChunked = [...this.iconsChunked, ...nextChunk];

                this.expanded = false;
                this.chunk += chunkSize;
            },
            displayIcon(icon) {
                if (this.filter.search === "") {
                    return true;
                }
                let keyword = this.filter.search.toUpperCase();
                let alt = keyword.replace("-", " ");
                let name = icon.iconName.toUpperCase();
                let nameAlt = name.replace("-", " ");

                return (
                    name.includes(keyword) ||
                    name.indexOf(keyword) !== -1 ||
                    nameAlt.includes(alt) ||
                    nameAlt.indexOf(alt) !== -1
                );
            },
            displayFilter(typeset) {
                return (
                    this.filter.type == "" ||
                    this.filter.type == "all" ||
                    this.filter.type == typeset
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
                this.chunk = 0;
                this.iconsChunked = [];
                this.iconContainer.scrollTop = 0;
                this.filter.type = "";
                this.filter.search = "";
            },

            closeModal() {
                this.modalOpen = false;
                this.clearFilter();
                this.handleClose();
            },

            toggleModal() {
                this.modalOpen = !this.modalOpen;
                this.clearFilter();
            },

            saveIcon(icon) {
                let fa6_prefixes = {
                    fas: "fa-solid",
                    far: "fa-regular",
                    fal: "fa-light",
                    fat: "fa-thin",
                    fab: "fa-brands",
                    fad: "fa-duotone",
                };
                let old_prefix = icon.prefix;
                let fa6_prefix = fa6_prefixes[old_prefix];

                this.value = fa6_prefix + " fa-" + icon.iconName;

                this.clearFilter();
                this.handleConfirm();
            },

            handleClose() {
                this.$emit("close");
            },
            handleConfirm() {
                this.$emit("confirm", this.value);
            },

            /*
             * Convert the class to string
             */
            definitionToString(def) {
                switch (def) {
                    case "far":
                    case "fa-regular":
                        return this.__("novaFontawesome.types.regular");
                        break;
                    case "fas":
                    case "fa-solid":
                        return this.__("novaFontawesome.types.solid");
                        break;
                    case "fab":
                    case "fa-brands":
                        return this.__("novaFontawesome.types.brands");
                        break;
                    case "fal":
                    case "fa-light":
                        return this.__("novaFontawesome.types.light");
                        break;
                    case "fad":
                    case "fa-duotone":
                        return this.__("novaFontawesome.types.duotone");
                        break;
                    case "fat":
                    case "fa-thin":
                        return this.__("novaFontawesome.types.thin");
                        break;
                }
            },

            /*
             * Convert the string to class method
             */
            stringToDefinition(str) {
                switch (str) {
                    case this.__("novaFontawesome.types.regular"):
                        return "far";
                        break;
                    case this.__("novaFontawesome.types.solid"):
                        return "fas";
                        break;
                    case this.__("novaFontawesome.types.brands"):
                        return "fab";
                        break;
                    case this.__("novaFontawesome.types.light"):
                        return "fal";
                        break;
                    case this.__("novaFontawesome.types.duotone"):
                        return "fad";
                        break;
                    case this.__("novaFontawesome.types.thin"):
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
                return (
                    this.field.add_button_text ||
                    this.__("novaFontawesome.addIcon")
                );
            },
            enforceDefaultIcon() {
                return this.field.enforce_default_icon || false;
            },
            defaultIconOutput() {
                return this.defaultIconType + " fa-" + this.defaultIcon;
            },
            chunkedIcons() {
                return this.iconsChunked;
            },
        },

        watch: {
            "filter.search": {
                handler(val) {
                    this.filter.search = val;
                    this.chunk = 0;
                    this.iconsChunked = [];
                    this.getIcons();
                },
            },
            "filter.type": {
                handler(val) {
                    if (this.old_filter.type !== val) {
                        this.clearFilter();
                    }
                    this.filter.type = val;
                    this.chunk = 0;
                    this.iconsChunked = [];
                    this.old_filter.type = this.filter.type;
                    this.getIcons();
                },
            },
        },
    };
</script>

<style scoped></style>
