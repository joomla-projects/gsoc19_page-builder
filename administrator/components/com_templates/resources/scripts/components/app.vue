<template>
<div class="pagebuilder row">
  <div class="col-2" absolute temporary id="Settings">
    <h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
    <hr>
        <!-- Settings for editing positions -->
        <edit-position v-if="edit_position" class="form-group" :grid="grid_selected" :column="column_selected" @reset="reset"></edit-position>

        <!-- Settings for editing grids -->
        <edit-grid v-else-if="edit_grid" class="form-group" :grid="grid_selected" @reset="reset"></edit-grid>

        <!-- Settings for adding columns -->
        <add-column v-else-if="add_column" class="form-group" :grid="grid_selected" @reset="reset"></add-column>

        <div v-else>Select an element to view Settings</div>
  </div>

  <div id="View" class="col-10">
    <h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
    <!-- Grid -->
    <draggable v-model="gridArray" ghost-class="drop">
        <div v-for="grid in gridArray" v-model="gridArray" class="row-wrapper">
            <div class="btn-wrapper">
				<button v-if="gridArray.length" type="button" class="btn btn-lg" @click="editGrid(grid)">
                    <span class="icon-options" title="Edit Grid"></span>
                </button>
				<button v-if="gridArray.length" type="button" class="btn btn-lg" @click="deleteGrid(grid)">
                    <span class="icon-cancel" title="Delete Grid"></span>
                </button>
            </div>

            <!-- Column -->
            <draggable v-model="grid.children" class="row">
                <div class="col-wrapper" v-for="column in grid.children" :class="[column.options.size]">
                    <div class="btn-wrapper">
						<button type="button" class="btn btn-lg" @click="editColumn(column)">
                            <span class="icon-options" title="Edit Column"></span>
                        </button>
						<button type="button" class="btn btn-lg" @click="deleteColumn(grid,column)">
                            <span class="icon-cancel" title="Delete Column"></span>
                        </button>
                    </div>

                    <span>{{column.options.size}} (<i>{{column.type}}<span v-if="column.options.class">, .{{column.options.class}}</span></i>)</span>
                    <div class="row">
                        <button type="button" class="btn btn-add btn-outline-info" data-toggle="modal" data-target="#newmodule">
                            <span class="icon-new"></span>
                            {{ translate('COM_TEMPLATES_ADD_MODULE') }}
                        </button>
                    <!-- TODO: Add Module Position -->
                        <button type="button" class="btn btn-lg edit-module" @click="editPosition(grid,column)">
                          <span class="icon-options" title="Edit Module"></span>
                        </button>
                    </div>
                </div>
            </draggable>

            <button class="btn btn-add btn-outline-info" type="button" @click="addColumn(grid)">
                <span class="icon-new"></span>
                {{ translate('COM_TEMPLATES_ADD_COLUMN') }}
            </button>
            <!-- Column Ends-->
        </div>
    </draggable>
    <!-- Grid Ends -->

    <button type="button" class="btn btn-outline-info btn-block" @click="show('add-grid')">
        <span class="icon-new"></span>
        {{ translate('COM_TEMPLATES_ADD_GRID') }}
    </button>

    <!-- Modals -->
    <add-grid-modal id="add-grid" @selection="addGrid"></add-grid-modal>
    <edit-column-modal id="edit-column" :column="currentColumn"></edit-column-modal>

    <!-- Modal for adding modules -->
    <div class="modal fade" id="newmodule" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>{{ translate('COM_TEMPLATES_SELECT_MODULE') }}</h5>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal ends -->
  </div>
</div>
</template>

<script>
  import draggable from 'vuedraggable';

  export default {
    props: {
      grid: {
        type: Array,
        required: false,
        default: function () {
            return [];
        },
      },
    },
    data() {
      return {
        currentColumn: {options:{class:''}},
        myArray: [],
        add_column: false,
        edit_grid: false,
        edit_position: false,
        grid_selected: '',
        column_selected: '',
        gridArray: this.grid,
        showSettings: false
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
                    size: 'col-' + size
                },
                children: [{
                    type: 'position',
                    options: {
                        module_chrome: 'none'
                    },
                    children: []
                }]
            });
        });
        this.gridArray.push({
            type: 'grid',
            options: {},
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
        this.add_column = true;
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
        this.edit_position = true;
      },
      editColumn(column) {
        this.currentColumn = column;
        this.show('edit-column');
      },
      editGrid(grid) {
        this.reset();
        this.showSettings = true;
        this.edit_grid = true;
        this.grid_selected = grid;
      },
      reset() {
        this.edit_grid = false;
        this.edit_position = false;
        this.showSettings = false;
        this.add_column = false;
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
    }
  };
</script>
