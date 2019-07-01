<template>
	<modal name="add-element" role="dialog" :classes="['modal-content', 'v--modal']" height="auto" @before-open="reset">
		<div class="modal-header">
			<h3 class="modal-title">{{ translate('COM_TEMPLATES_SELECT_ELEMENT') }}</h3>
			<button @click="close" type="button" class="close" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
            <div class="row">
                <div class="nav flex-column nav-pills" id="nav-tab" role="tablist">
                    <a class="nav-link" data-toggle="tab" v-for="element in allowedChildren" :href="'#nav-'+element.name" role="tab">{{ element.name }}</a>
                </div>
                <div class="tab-content col-9">
                    <div class="tab-pane" v-for="element in allowedChildren" :id="'nav-'+element.name">{{ element.description }}
                        <div v-if="element.name == 'Grid'" class="image-selection">
                            <div class="row">
                                <div class="col icon" v-html="images.row12 + '<p>100%</p>'" @click="$emit('selection', 'Grid' , [12])"></div>
                                <div class="col icon" v-html="images.row66 + '<p>(50%-50%)</p>'" @click="$emit('selection', 'Grid' , [6, 6])"></div>
                                <div class="col icon" v-html="images.row48 + '<p>(33%-67%)</p>'" @click="$emit('selection', 'Grid' , [4, 8])"></div>
                            </div>
                            <div class="row">
                                <div class="col icon" v-html="images.row84 + '<p>(67%-33%)</p>'" @click="$emit('selection', 'Grid' , [8, 4])"></div>
                                <div class="col icon" v-html="images.row3333 + '<p>(25%-25%-25%-25%)</p>'" @click="$emit('selection', 'Grid' , [3, 3, 3, 3])"></div>
                                <div class="col icon" v-html="images.row444 + '<p>(33%-33%-33%)</p>'" @click="$emit('selection', 'Grid' , [4, 4, 4])"></div>
                            </div>
                            <div class="row">
                                <div class="col-4 icon" v-html="images.row363 + '<p>(25%-50%-25%)</p>'" @click="$emit('selection', 'Grid' , [3, 6, 3])"></div>
                            </div>
                        </div>
                    </div>
                </div>
		    </div>
            <small class="form-text text-muted">{{ translate('COM_TEMPLATES_ADD_ELEMENT_DESC') }}</small>
        </div>
        <div class="modal-footer">
            <div class="btn-group">
                <button type="button" class="btn btn-success" @click="add">{{ translate('COM_TEMPLATES_SAVE') }}</button>
                <button type="button" class="btn btn-danger" @click="close">{{ translate('JTOOLBAR_BACK') }}</button>
            </div>
        </div>
	</modal>
</template>

<script>
  export default {
    name: 'modal-add-element',
    data() {
        return {
            selectElem: '',
            images: window.Joomla.getOptions('com_templates').images,
            elements: window.Joomla.getOptions('com_templates').elements,
        }
    },
    props: {
        allowedChildren: {
            type: Object,
        },
    },
    methods: {
        add() {
            if(this.selectElem !== '') {
                this.$emit('selection', this.selectElem);
                this.close();
            }
        },
        close() {
            this.$modal.hide('add-element');
        },
        reset() {
            this.selectElem = '';
        }
    },
  }
</script>
