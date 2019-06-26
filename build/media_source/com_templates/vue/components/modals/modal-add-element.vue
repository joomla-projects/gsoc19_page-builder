<template>
	<modal name="add-element" role="dialog" :classes="['modal-content', 'v--modal']" height="auto">
		<div class="modal-header">
			<h3 class="modal-title">{{ translate('COM_TEMPLATES_SELECT_ELEMENT') }}</h3>
			<button @click="close" type="button" class="close" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
            <fieldset>
                <div class="form-group">
                    <label for="select_element">{{ translate('COM_TEMPLATES_SELECT_ELEMENT') }}</label>
                    <select name="select_element" id="select_element" v-model="selectElem">
                        
                        <option v-for="elem in elements">{{elem.name}}</option>

                    </select>
                </div>
            </fieldset>
            <div v-if="selectElem == 'Grid'" class="row image-selection">
                <div class="col icon" v-html="images.row12" @click="$emit('selection', 'Grid' , [12])"></div>
				<div class="col icon" v-html="images.row66" @click="$emit('selection', 'Grid' , [6, 6])"></div>
				<div class="col icon" v-html="images.row48" @click="$emit('selection', 'Grid' , [4, 8])"></div>
				<div class="col icon" v-html="images.row84" @click="$emit('selection', 'Grid' , [8, 4])"></div>
				<div class="col icon" v-html="images.row3333" @click="$emit('selection', 'Grid' , [3, 3, 3, 3])"></div>
				<div class="col icon" v-html="images.row444" @click="$emit('selection', 'Grid' , [4, 4, 4])"></div>
				<div class="col icon" v-html="images.row363" @click="$emit('selection', 'Grid' , [3, 6, 3])"></div>
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
        }
    },
    props: {
        elements: {
            type: Array
        },
        parent: {},
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
    },
  };
</script>
