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
			<draggable v-model="gridArray" ghost-class="drop">
				<div v-for="grid in gridArray" class="row-wrapper">
					<div class="btn-wrapper">
						<button type="button" class="btn btn-lg" @click="editGrid(grid)">
							<span class="icon-options"></span>
							<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_GRID') }}</span>
						</button>
						<button type="button" class="btn btn-lg" @click="deleteGrid(grid)">
							<span class="icon-cancel"></span>
							<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_GRID') }}</span>
						</button>
					</div>
					<span v-if="grid.options">.{{grid.options.class}}</span>

					<!-- Column -->
					<draggable v-model="grid.children" class="row">
						<div class="col-wrapper" v-for="column in grid.children" :class="[column.options.size]">
							<div class="btn-wrapper">
								<button type="button" class="btn btn-lg" @click="editColumn(column)">
									<span class="icon-options"></span>
									<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
								</button>
								<button type="button" class="btn btn-lg" @click="deleteColumn(grid,column)">
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

					<button class="btn btn-add btn-outline-info" type="button" @click="addColumn(grid)">
						<span class="icon-new"></span>
						{{ translate('COM_TEMPLATES_ADD_COLUMN') }}
					</button>
				</div>
			</draggable>
			<!-- Grid Ends -->

			<button type="button" class="btn btn-outline-info btn-block" @click="show('add-grid')">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_GRID') }}
			</button>

			<!-- Modals -->
			<add-grid-modal id="add-grid" @selection="addGrid"></add-grid-modal>
			<add-element-modal id="add-element" :elements="elements" :column="column_selected"></add-element-modal>
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
        gridArray: this.grid,
        showSettings: false,
        selectedSettings: '',
		    elements: window.Joomla.getOptions('com_templates').elements,
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
            type: 'column',
            options: {
              size: 'col-' + size,
              class: ''
            },
            children: []
          });
        });
        this.gridArray.push({
          type: 'grid',
          options: {
            class: '',
          },
          children: this.myArray
        });
        this.reset();
        this.hide('add-grid');
      },
      deleteGrid(grid) {
        const index = this.gridArray.indexOf(grid);
        if (index > -1)
          this.gridArray.splice(index, 1);
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
      addElement(column) {
        this.column_selected = column;
        this.show('add-element');
      }
    }
  };
</script>
