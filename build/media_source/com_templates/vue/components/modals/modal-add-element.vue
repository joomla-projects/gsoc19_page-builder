<template>
	<modal name="add-element" role="dialog" :classes="['modal-content', 'v--modal']"
			height="500" width="800px"
			:clickToClose="false" @opened="opened">
		<div class="modal-header">
			<h3 class="modal-title">{{ translate('COM_TEMPLATES_SELECT_ELEMENT') }}</h3>
			<button @click="$modal.hide('add-element')" type="button" class="close" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-4">
					<div class="list-group" id="list-tab" role="tablist">
						<li v-for="element in allowedChildren" data-toggle="list" role="tabpanel"
							class="element-selection list-group-item list-group-item-action"
							:href="'#list-'+element.name">
							{{ element.name }}
						</li>
					</div>
				</div>
				<div class="col-8 tab-content">
					<div v-for="element in allowedChildren" :id="'list-'+element.name" class="tab-pane fade" role="tabpanel">
						{{ element.description }}
						<div v-if="element.name === 'Grid'" class="image-selection">
							<div class="row">
								<div class="col-4 icon" v-html="images.row12 + '<p>100%</p>'"
									@click="insertElement('Grid' , [12])"></div>
								<div class="col-4 icon" v-html="images.row66 + '<p>(50%-50%)</p>'"
									@click="insertElement('Grid' , [6, 6])"></div>
								<div class="col-4 icon" v-html="images.row48 + '<p>(33%-67%)</p>'"
									@click="insertElement('Grid' , [4, 8])"></div>
								<div class="col-4 icon" v-html="images.row84 + '<p>(67%-33%)</p>'"
									@click="insertElement('Grid' , [8, 4])"></div>
								<div class="col-4 icon" v-html="images.row3333 + '<p>(25%-25%-25%-25%)</p>'"
									@click="insertElement('Grid' , [3, 3, 3, 3])"></div>
								<div class="col-4 icon" v-html="images.row444 + '<p>(33%-33%-33%)</p>'"
									@click="insertElement('Grid' , [4, 4, 4])"></div>
								<div class="col-4 icon" v-html="images.row363 + '<p>(25%-50%-25%)</p>'"
									@click="insertElement('Grid' , [3, 6, 3])"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<small class="form-text text-muted">{{ translate('COM_TEMPLATES_ADD_ELEMENT_DESC') }}</small>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" @click="$modal.hide('add-element')">
				{{ translate('JTOOLBAR_CLOSE') }}
			</button>
			<button type="button" class="btn btn-success" @click="add">
				{{ translate('COM_TEMPLATES_SAVE') }}
			</button>
		</div>
	</modal>
</template>

<script>
  import {mapState, mapMutations} from 'vuex';

  export default {
    name: 'modal-add-element',
    data() {
      return {
        images: window.Joomla.getOptions('com_templates').images,
      };
    },
    computed: {
      ...mapState([
        'allowedChildren'
      ])
    },
    methods: {
      ...mapMutations([
        'addGrid'
      ]),
      add() {
        const selection = document.querySelector('.element-selection.active');
        if (selection) {
          this.insertElement(selection.innerText);
          this.$modal.hide('add-element');
        }
      },
      insertElement(element, sizes) {
        if (element === 'Grid') {
          this.addGrid(sizes);
        } else {
          this.$store.commit('insertElement', element);
        }
        this.$modal.hide('add-element');
      },
      opened() {
        const firstListItem = document.querySelector('.list-group .list-group-item');
        firstListItem && firstListItem.classList.add('active');

        const content = document.querySelector('.tab-content .tab-pane');
        if (content) {
          content.classList.add('show');
          content.classList.add('active');
        }
      }
    },
  };
</script>
