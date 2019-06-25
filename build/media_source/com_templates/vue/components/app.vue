<template>
	<div>
		<v-navigation-drawer v-model="showSettings" app id="Settings" class="settings">
			<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
      <component :is="selectedSettings" class="form-group" :grid='grid_selected' :column='column_selected' @reset="reset"></component>
		</v-navigation-drawer>

		<v-content class="pagebuilder">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
			<!-- Grid -->
			<draggable v-model="elementArray" ghost-class="drop">
				<div v-for="element in elementArray" class="row-wrapper">
          <span>{{ element.type }}</span>
					<div class="btn-wrapper">
						<button type="button" class="btn btn-lg" @click="editGrid(element)">
							<span class="icon-options"></span>
							<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_GRID') }}</span>
						</button>
						<button type="button" class="btn btn-lg" @click="deleteGrid(element)">
							<span class="icon-cancel"></span>
							<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_GRID') }}</span>
						</button>
					</div>
					<span v-if="element.options">.{{element.options.class}}</span>

					<!-- Column -->
          <div  v-if="element.type == 'Grid'">
            <draggable v-model="element.children" class="row">
              <div class="col-wrapper" v-for="column in element.children" :class="[column.options.size]">
                <div class="btn-wrapper">
                  <button type="button" class="btn btn-lg" @click="editColumn(column)">
                    <span class="icon-options"></span>
                    <span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
                  </button>
                  <button type="button" class="btn btn-lg" @click="deleteColumn(element,column)">
                    <span class="icon-cancel"></span>
                    <span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_COLUMN') }}</span>
                  </button>
                </div>

                <span>{{column.options.size}} (<i>{{column.type}}<span v-if="column.options.class">, .{{column.options.class}}</span></i>)</span>
                  <button type="button" class="btn btn-add btn-outline-info" @click="addElement(column)">
                    <span class="icon-new"></span>
                    {{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
                  </button>
              </div>
            </draggable>
					<!-- Column Ends-->

            <button class="btn btn-add btn-outline-info" type="button" @click="addColumn(element)">
              <span class="icon-new"></span>
              {{ translate('COM_TEMPLATES_ADD_COLUMN') }}
            </button>
          </div>
				</div>
			</draggable>
			<!-- Grid Ends -->

			<button type="button" class="btn btn-outline-info btn-block" @click="addElement('root')">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
			</button>
      {{elementArray}}
			<!-- Modals -->
			<add-element-modal id="add-element" :elements="elements" :parent="parent" @selection="insertElem"></add-element-modal>
		</v-content>
	</div>
</template>

<script>
  import draggable from 'vuedraggable';

  export default {
    props: {
      grid: {
        default: function () {
          return JSON.parse(document.getElementById('jform_params_grid').value);
        },
      },
    },
    data() {
      return {
        myArray: [],
        grid_selected: '',
        column_selected: '',
        elementArray: this.grid,
        showSettings: false,
        selectedSettings: '',
        elements: window.Joomla.getOptions('com_templates').elements,
        parent: ''
      };
    },
    watch: {
      grid: {
        handler: function (newVal) {
          document.getElementById('jform_params_grid').value = JSON.stringify(newVal);
        },
        deep: true,
      },
    },
    components: {
        draggable
    },
    methods: {
      addGrid(sizes) {
        this.myArray = [];
        sizes.forEach(size => {
          this.myArray.push({
            type: 'Column',
            options: {
              size: 'col-' + size,
              class: ''
            },
            children: []
          });
        });
        this.elementArray.push({
          type: 'Grid',
          options: {
            class: '',
          },
          children: this.myArray
        });
        this.reset();
        this.hide('add-grid');
      },
      deleteGrid(grid) {
        const index = this.elementArray.indexOf(grid);
        if (index > -1)
          this.elementArray.splice(index, 1);
      },
      addColumn(grid) {
        this.reset();
        this.showSettings = true;
        this.grid_selected = grid;
        this.selectedSettings = 'add-column';
      },
      deleteColumn(grid, column) {
        const index = grid.children.indexOf(column);
        if (index > -1) {
          grid.children.splice(index, 1);
        }
      },
      editPosition(grid, column) {
        this.reset();
        this.showSettings = true;
        this.grid_selected = grid;
        this.column_selected = column;
        this.selectedSettings = 'edit-position';
      },
      editColumn(column) {
        this.reset();
        this.showSettings = true;
        this.column_selected = column;
        this.selectedSettings = 'edit-column';
      },
      editGrid(grid) {
        this.reset();
        this.showSettings = true;
        this.selectedSettings = 'edit-grid';
        this.grid_selected = grid;
      },
      reset() {
        this.showSettings = false;
        this.grid_selected = '';
        this.column_selected = '';
      },
      log(el) {
        console.log(el);
      },
      show(name) {
        this.$modal.show(name);
      },
      hide(name) {
        this.$modal.hide(name);
      },
      addContainer() {
        this.elementArray.push({
          type: 'Container',
          options: {
            class: ''
          },
          children: []
        })
      },
      addElement(parent) {
        this.parent = parent;
        this.show('add-element');
      },
      insertElem(element,sizes) {
        if(element == 'Grid') {
          this.addGrid(sizes);
        }
        else if(element == 'Container') {
          this.addContainer();
        }
        this.hide('add-element');
      }
    }
  };
</script>
