<template>
    <div>
        <!-- Settings for editing elements -->
        <fieldset>
            <legend>{{ translate('COM_TEMPLATES_EDIT') }}{{ elementSelected.type }}</legend>
            <div class="form-group">
                <label for="element_class">{{ translate('COM_TEMPLATES_ADD_CLASS') }}</label>
                <input type="text" name="element_class" id="element_class" class="form-control" v-model="element_class">
                <hr>
                <div v-if="elementSelected.type === 'column'">
                    <label for="element_offset">{{ translate('COM_TEMPLATES_ADD_OFFSET') }}</label>
                    <br>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Device</th>
                                <th scope="col">Offset</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row"><i class="fas fa-mobile-alt fa-2x"></i></th>
                                <td class="control-group">
                                    <select name="element_offset" id="element_offset" class="custom-select custom-select-sm" v-model="element_offset.xs">
                                        <option v-for="opt in offset" v-if="opt.value <= threshold" :value="opt.value">
                                            {{opt.label}}
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><i class="fas fa-tablet-alt fa-2x"></i></th>
                                <td class="control-group">
                                    <select name="element_offset" id="element_offset" class="custom-select custom-select-sm" v-model="element_offset.sm">
                                        <option v-for="opt in offset" v-if="opt.value <= threshold" :value="opt.value">
                                            {{opt.label}}
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><i class="fas fa-laptop fa-2x"></i></th>
                                <td class="control-group">
                                    <select name="element_offset" id="element_offset" class="custom-select custom-select-sm" v-model="element_offset.md">
                                        <option v-for="opt in offset" v-if="opt.value <= threshold" :value="opt.value">
                                            {{opt.label}}
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><i class="fas fa-desktop fa-2x"></i></th>
                                <td class="control-group">
                                    <select name="element_offset" id="element_offset" class="custom-select custom-select-sm" v-model="element_offset.lg">
                                        <option v-for="opt in offset" v-if="opt.value <= threshold" :value="opt.value">
                                            {{opt.label}}
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-for="attrib in elementSelected.attributes" :key="attrib.name" class="form-group">
                <label :for="attrib.name">{{ attrib.label }}</label>
                <input v-model="attrib.value" :type="attrib.type" :id="attrib.name" :name="attrib.name" class="form-control">
            </div>
            <div>
                <button type="button" class="btn btn-success" @click="modifyElement">{{ translate('COM_TEMPLATES_ADD') }}</button>
                <button type="button" class="btn btn-secondary" @click="closeNav">{{ translate('JTOOLBAR_CLOSE') }}</button>
            </div>
        </fieldset>
    </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';

export default {
    name: 'edit-element',
    computed: {
        ...mapState([
            'elementSelected',
            'parent'
        ])
    },
    watch: {
        elementSelected() {
            this.threshold = this.parent[0].attributes.size.value - this.elementSelected.options.size;
            this.element_class = this.elementSelected.options.class;
            if(this.elementSelected.options.offset)
                this.element_offset = this.elementSelected.options.offset;
        }
    },
    data() {
        return {
            element_class: '',
            element_offset: {
                xs: '',
                sm: '',
                md: '',
                lg: ''
            },
            threshold: '',
            offset: [
                {
                    value: '',
                    label: 'No offset'
                },
                {
                    value: 1,
                    label: '1 column - 1/12'
                },
                {
                    value: 2,
                    label: '2 columns - 1/6'
                },
                {
                    value: 3,
                    label: '3 columns - 1/4'
                },
                {
                    value: 4,
                    label: '4 columns - 1/3'
                },
                {
                    value: 5,
                    label: '5 columns - 5/12'
                },
                {
                    value: 6,
                    label: '6 columns - 1/2'
                },
                {
                    value: 7,
                    label: '7 columns - 7/12'
                },
                {
                    value: 8,
                    label: '8 columns - 2/3'
                },
                {
                    value: 9,
                    label: '9 columns - 3/4'
                },
                {
                    value: 10,
                    label: '10 columns - 5/6'
                },
                {
                    value: 11,
                    label: '11 columns - 11/12'
                },
                {
                    value: 12,
                    label: '12 columns - 1/1'
                }
            ]
        }
    },
    created() {
        this.threshold = this.parent[0].attributes.size.value - this.elementSelected.options.size;
    },
    methods: {
        ...mapMutations([
            'closeNav'
        ]),
        modifyElement() {
            let modify = {};
            modify.class = (this.element_class !== '') ?  this.element_class : '';
            modify.offset = (this.element_offset !== '') ?  this.element_offset : '';
            modify.offsetClass = (this.element_offset.xs ? 'offset-xs-' + this.element_offset.xs : '')
                                + (this.element_offset.sm ? ' offset-sm-' + this.element_offset.sm : '')
                                + (this.element_offset.md ? ' offset-md-' + this.element_offset.md : '')
                                + (this.element_offset.lg ? ' offset-lg-' + this.element_offset.lg : '');
            this.$store.commit('modifyElement', modify);
        }
    },
}
</script>
