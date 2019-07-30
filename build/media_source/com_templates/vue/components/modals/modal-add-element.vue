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
							:data-id="element.id"
							:href="'#list-'+element.id">
							{{ element.title }}
						</li>
					</div>
				</div>
				<div class="col-8 tab-content">
					<div v-for="element in allowedChildren" :id="'list-'+element.id" class="tab-pane fade" role="tabpanel">
						{{ element.description }}
						<div v-if="element.id === 'grid'" class="image-selection">
							<div class="row">
								<div class="col-4 icon" v-html="images.row12 + '<p>100%</p>'"
									@click="selectGrid([12])" :class="{active: select == [12]}"></div>
								<div class="col-4 icon" v-html="images.row66 + '<p>(50%-50%)</p>'"
									@click="selectGrid([6, 6])" :class="{active: select == [6, 6]}"></div>
								<div class="col-4 icon" v-html="images.row48 + '<p>(33%-67%)</p>'"
									@click="selectGrid([4, 8])" :class="{active: select == [4, 8]}"></div>
								<div class="col-4 icon" v-html="images.row84 + '<p>(67%-33%)</p>'"
									@click="selectGrid([8, 4])" :class="{active: select == [8, 4]}"></div>
								<div class="col-4 icon" v-html="images.row3333 + '<p>(25%-25%-25%-25%)</p>'"
									@click="selectGrid([3, 3, 3, 3])" :class="{active: select == [3, 3, 3, 3]}"></div>
								<div class="col-4 icon" v-html="images.row444 + '<p>(33%-33%-33%)</p>'"
									@click="selectGrid([4, 4, 4])" :class="{active: select == [4, 4, 4]}"></div>
								<div class="col-4 icon" v-html="images.row363 + '<p>(25%-50%-25%)</p>'"
									@click="selectGrid([3, 6, 3])" :class="{active: select == [3, 6, 3]}"></div>
							</div>
						</div>
						<div v-if="element.id === 'moduleposition'">
							<label for="moduleposition_name">{{ translate('COM_TEMPLATES_POSITION_NAME') }}</label>
							<input type="text" name="moduleposition_name" id="moduleposition_name" class="form-control" v-model="moduleposition_name">
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
				{{ translate('COM_TEMPLATES_INSERT') }}
			</button>
		</div>
	</modal>
</template>

<script>
  import {mapState} from 'vuex';

  export default {
    name: 'modal-add-element',
    data() {
      return {
		images: window.Joomla.getOptions('com_templates').images,
		config: '',
		select: [],
		moduleposition_name: ''
      };
    },
    computed: {
      ...mapState([
        'allowedChildren'
      ])
    },
    methods: {
      add() {
        const selection = document.querySelector('.element-selection.active');
        if (selection) {
          this.$store.commit('addElement', {
            name: selection.dataset.id,
			config: this.config,
			moduleposition_name: this.moduleposition_name,
          });
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
	  },
	  selectGrid(config) {
		this.config = config;
		this.select = config.toString();
	  }
    },
  };
</script>
