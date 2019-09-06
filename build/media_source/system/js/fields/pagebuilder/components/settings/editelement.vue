<template>
	<div>
		<!-- Settings for editing elements -->
		<div>
			<legend>{{ translate('JLIB_PAGEBUILDER_EDIT') }}{{ elementSelected.type }}</legend>
			<div class="form-group">
				<label for="element_class">{{ translate('JLIB_PAGEBUILDER_ADD_CLASS') }}</label>
				<div class="">
					<input type="text" name="element_class" id="element_class" class="class_input"
						   :placeholder="translate('JLIB_PAGEBUILDER_NONE')" v-model="element_class">
					<span class="fa fa-plus add_class_button hoverCursor" @click="add"
						  title="Add" aria-hidden="true"></span>
				</div>
			</div>

			<div class="form-group">
				<div v-if="elementSelected.type === 'column'">
					<label for="element_offset">{{ translate('JLIB_PAGEBUILDER_ADD_OFFSET') }}</label>
					<br>
					<table class="table">
						<thead>
						<tr>
							<th scope="col">{{ translate('JLIB_PAGEBUILDER_DEVICE') }}</th>
							<th scope="col">{{ translate('JLIB_PAGEBUILDER_OFFSET') }}</th>
						</tr>
						</thead>
						<tbody>
						<tr v-for="size in offset_sizes">
							<th scope="row"><i :class="['fas', 'fa-' + size.icon, 'fa-lg', 'fa-rotate-' + size.rotate]"
											   class="icons_pictures"></i></th>
							<td class="control-group">
								<select name="element_offset" id="element_offset" class="custom-select custom-select-sm"
										v-model="element_offset[size.label]">
									<option v-for="opt in offset" v-if="opt.value <= threshold" :value="opt.value">
										{{opt.label}}
									</option>
								</select>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div v-for="(config, id) in configs" class="form-group">
					<label :for="id">{{ config.label }}</label>
					<select v-if="config.type === 'select'" :id="id" :name="id" class="custom-select"
							:required="config.required" v-model="elementSelected.options[id]">
						<option v-for="(value, key) in config.value" :value="value">{{ key }}</option>
					</select>
					<input v-else :id="id" @name="id" :type="config.type" class="form-control"
						   :required="config.required" v-model="elementSelected.options[id]"/>
				</div>
			</div>

			<div class="buttonsOnBottom">
				<button type="button" class="btn btn-secondary" @click="closeNav">{{ translate('JTOOLBAR_CLOSE') }}
				</button>
			</div>
		</div>
	</div>
</template>

<script>
    import {mapState, mapMutations} from 'vuex';

    export default {
        name: 'edit-element',
        computed: {
            ...mapState([
                'activeDevice',
                'elementSelected',
                'elements',
                'parent',
                'size'
            ]),
            threshold() {
                return this.size - this.elementSelected.options.size[this.activeDevice];
            },
            configs() {
                return this.elements.find(el => el.id === this.elementSelected.type).config;
            },
        },
        mounted() {
            this.element_class = this.elementSelected.options.class;
            if (this.elementSelected.options.offset)
                this.element_offset = this.elementSelected.options.offset;
        },
        watch: {
            elementSelected: {
                deep: true,
                handler() {
                    this.element_class = this.elementSelected.options.class;
                    if (this.elementSelected.options.offset)
                        this.element_offset = this.elementSelected.options.offset;
                },
            }
        },
        data() {
            return {
                element_class: {
                    name: '',

				},
                element_offset: {},
                offset_sizes: [
                    {
                        icon: 'mobile-alt',
                        label: 'xs',
                        rotate: '0'
                    },
                    {
                        icon: 'tablet-alt',
                        label: 'sm',
                        rotate: '0'
                    },
                    {
                        icon: 'tablet-alt',
                        label: 'md',
                        rotate: '270'
                    },
                    {
                        icon: 'laptop',
                        label: 'lg',
                        rotate: '0'
                    },
                    {
                        icon: 'desktop',
                        label: 'xl',
                        rotate: '0'
                    },
                ],
                offset: [
                    {
                        value: 0,
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
        methods: {
            ...mapMutations([
                'closeNav',
                'modifyElement'
            ]),
            add() {
                let modify = {};
                modify.class = (this.element_class !== '') ? this.element_class : '';
                modify.offset = this.element_offset;
                this.modifyElement(modify);
            }
        },
    }
</script>

<style lang="scss">

	.fa-rotate-270 {
		margin-top: 0px;
	}

	.icons_pictures {
		margin-top: 15px;
	}

	.buttonsOnBottom {
		float: right;
	}

	.add_class_div {
		display: inline;
	}

	.class_input {
		padding: 5px;
	}

	.add_class_button {
		margin-left: 15px;
	}

	.hoverCursor:hover {
		cursor: pointer;
	}

</style>
