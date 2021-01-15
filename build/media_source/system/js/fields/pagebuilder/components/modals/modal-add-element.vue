<template>
	<modal name="add-element" role="dialog" :classes="['modal-content', 'v--modal']"
			height="70%" width="90%" :style="{ 'overflow-y': 'auto' }"
			:clickToClose="false" @opened="opened">
		<div class="modal-header">
			<h3 class="modal-title">{{ translate('JLIB_PAGEBUILDER_SELECT_ELEMENT') }}</h3>
			<button @click="$modal.hide('add-element')" type="button" class="close" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-12">
					<div class="list-group" id="list-tab" role="tablist">
						<li v-for="element in allowedChildren" data-toggle="list" role="tabpanel"
							class="element-selection list-group-item list-group-item-action"
							:data-id="element.id"
							:href="'#list-'+element.id"
							@click="setElement(element.id)">
							{{ element.title }}
						</li>
					</div>
				</div>
				<div class="col-12 tab-content">
					<div v-for="element in allowedChildren" :id="'list-'+element.id" class="tab-pane fade" role="tabpanel">
						{{ element.description }}

						<!-- TODO: generalise grid config -->
						<div v-if="element.id === 'grid'" class="image-selection">
							<div class="row">
								<div class="col-6 col-sm-2 icon" v-html="images.row12 + '<p>100%</p>'"
									@click="selectGrid([12])" :class="{active: select == [12]}"></div>
								<div class="col-6 col-sm-2  icon" v-html="images.row66 + '<p>(50%-50%)</p>'"
									@click="selectGrid([6, 6])" :class="{active: select == [6, 6]}"></div>
								<div class="col-6 col-sm-2 icon" v-html="images.row48 + '<p>(33%-67%)</p>'"
									@click="selectGrid([4, 8])" :class="{active: select == [4, 8]}"></div>
								<div class="col-6 col-sm-2  icon" v-html="images.row84 + '<p>(67%-33%)</p>'"
									@click="selectGrid([8, 4])" :class="{active: select == [8, 4]}"></div>
								<div class="col-6 col-sm-2  icon" v-html="images.row3333 + '<p>(25%-25%-25%-25%)</p>'"
									@click="selectGrid([3, 3, 3, 3])" :class="{active: select == [3, 3, 3, 3]}"></div>
								<div class="col-6 col-sm-2  icon" v-html="images.row444 + '<p>(33%-33%-33%)</p>'"
									@click="selectGrid([4, 4, 4])" :class="{active: select == [4, 4, 4]}"></div>
								<div class="col-6 col-sm-2  icon" v-html="images.row363 + '<p>(25%-50%-25%)</p>'"
									@click="selectGrid([3, 6, 3])" :class="{active: select == [3, 6, 3]}"></div>
							</div>
						</div>

						<div v-for="(conf, id) in element.config" v-if="conf.required" class="element-configuration">
							<label :for="id">
								{{ conf.label }}
								<span class="star" aria-hidden="true">&nbsp;*</span>
							</label>
							<select v-if="conf.type === 'select'" @id="id" @name="id" class="custom-select" required
									v-model="config[id]" @blur="check(element.id, $event)">
								<option v-for="(value, key) in conf.value" :value="key">{{ value }}</option>
							</select>
							<input v-else :type="conf.type" :id="id" :name="id" class="form-control" required
									:placeholder="conf.placeholder" v-model="config[id]" @blur="check(element.id, $event)"/>
						</div>
					</div>
				</div>
			</div>
			<small class="form-text text-muted">{{ translate('JLIB_PAGEBUILDER_ADD_ELEMENT_DESC') }}</small>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" @click="$modal.hide('add-element')">
				{{ translate('JTOOLBAR_CLOSE') }}
			</button>
			<button type="button" class="btn btn-success" @click="add">
				{{ translate('JLIB_PAGEBUILDER_INSERT') }}
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
        images: window.Joomla.getOptions('system.pagebuilder').images,
        selectedElement: '',
        config: '',
        childConfig: '',
        select: [],
      };
    },
    computed: {
      ...mapState([
        'allowedChildren'
      ])
    },
    methods: {
      add() {
        if (!this.validate()) {
          return;
        }

        this.$store.commit('addElement', {
          name: this.selectedElement,
          config: this.config,
          childConfig: this.childConfig,
        });

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

        this.selectedElement = this.allowedChildren[0].id;
        this.config = this.allowedChildren[0].config || [];
      },
      selectGrid(config) {
        this.childConfig = config;
        this.select = config.toString();
      },
      setElement(id) {
        this.selectedElement = id;
        this.config = {};

        // Set configuration to fill
        const type = this.allowedChildren.find(child => child.id === id);
        for (const key in type.config) {
          if (type.config.hasOwnProperty(key)) {
            this.config[key] = '';
          }
        }
      },
      check(id, event) {
        if (!event.target.value) {
          event.target.classList.add('invalid');
          document.querySelector(`#list-${id} label`).classList.add('invalid');
        } else {
          event.target.classList.remove('invalid');
          document.querySelector(`#list-${id} label`).classList.remove('invalid');
        }
      },
      validate() {
        const selected = document.querySelector(`#list-${this.selectedElement}`);

        // Check for invalid fields or labels (triggered
        if (selected.querySelector('.invalid')) {
          return false;
        }

        // Check for invalid values
        let valid = true;
        const configs = selected.querySelectorAll('.element-configuration');

        configs.forEach(config => {
          const field = config.querySelector('input, select');
          if (!field.value) {
            valid = false;
            field.classList.add('invalid');
            config.querySelector('label').classList.add('invalid');
          }
        });

        return valid;
      },
    },
  };
</script>
