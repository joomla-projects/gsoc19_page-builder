<template>
<div class="container-fluid row">
  <div v-if="showSettings" id="Settings" class="settings col-sm-2">
      <h2>Settings</h2>
      <hr>
      <form>
        <!-- Settings for editing positions -->
        <div v-if="edit_position">
            <fieldset>
                <legend>Edit module position - {{ selected_pos }}</legend>
                <div class="form-group">
                    <label for="module_chrome">Select module chrome</label>
                    <select id="module_chrome" name="module_chrome" v-model="module_chrome">
                      <option value="none">none</option>
                      <option value="rounded">rounded</option>
                      <option value="table">table</option>
                      <option value="horz">horz</option>
                      <option value="xhtml">xhtml</option>
                      <option value="html5">html5</option>
                      <option value="outline">outline</option>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-success" @click="save">Save</button>
                    <button type="button" class="btn btn-secondary" @click="back">Back</button>
                </div>
            </fieldset>
        </div>

        <!-- Settings for editing columns -->
        <div v-else-if="edit_column" class="btn-group">
          <fieldset>
            <legend>Edit Column</legend>
            <label for="column_class">Add a custom class</label>
            <input id="column_class" name="column_class" type="text" v-model="column_class">
            
            <button type="button" class="btn btn-success" @click="editColumn('','',true)">Save</button>
            <button type="button" class="btn btn-secondary" @click="back">Back</button>
          </fieldset>
        </div>

      <!-- Settings for editing grids -->
      <div v-else-if="edit_grid" class="form-group">
        <fieldset>
            <legend>Edit Grid</legend>
            <label for="column_size">Add new column</label>
            <input id="column_size" name="column_size" type="text" v-model="column_size">

            <label for="grid_class">Add a custom class</label>
            <input id="grid_class" name="grid_class" type="text" v-model="grid_class">

            <div class="btn-group">
                <button type="button" class="btn btn-success" @click="editGrid('',true)">Save</button>
                <button type="button" class="btn btn-danger" @click="back">Back</button>
            </div>
        </fieldset>
      </div>

    </form>
  </div>

  <div id="View" class="container-fluid">
    <h2>View</h2>
    <!-- Grid -->
    <draggable v-model="gridArray">
      <div v-for="grid in gridArray" v-model="gridArray" class="draggable">
        <button v-if="gridArray.length" type="button" class="icon-cancel close" @click="deleteGrid(grid)"></button>
        <button v-if="gridArray.length" type="button" class="icon-apply close" @click="editGrid(grid,false)"></button>

        <!-- Column -->
        <draggable v-model="grid.children" class="row grid-row">
          <div class="list-group-item" v-for="column in grid.children" :class="[column.options.size]" @click="">
            {{column.options.size}}
            (<i>{{column.type}}</i>)
            <button type="button" class="icon-cancel close" @click="deleteColumn(grid,column)"></button>
            <button type="button" class="icon-apply close" @click="editColumn(grid,column,false)"></button>
            <br>
            <button type="button" class="postion btn btn-outline-info btn-block" data-toggle="modal" data-target="#newmodule">+</button>
          </div>
        </draggable>
        <!-- Column Ends-->

      </div>
    </draggable>
    <!-- Grid Ends -->

    <button type="button" class="btn btn-outline-info btn-block" data-toggle="modal" data-target="#newgrid">+</button>

    <!-- Modal -->
    <div class="modal fade" id="newgrid" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Select layout</h5>
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            Predefined
            <div class="row">
              <div class="col-sm" v-html="images.row12" @click="grid_system = '12'"></div>
              <div class="col-sm" v-html="images.row66" @click="grid_system = '6 6'"></div>
              <div class="col-sm" v-html="images.row48" @click="grid_system = '4 8'"></div>
              <div class="col-sm" v-html="images.row84" @click="grid_system = '8 4'"></div>
              <div class="col-sm" v-html="images.row3333" @click="grid_system = '3 3 3 3'"></div>
              <div class="col-sm" v-html="images.row444" @click="grid_system = '4 4 4'"></div>
              <div class="col-sm" v-html="images.row363" @click="grid_system = '3 6 3'"></div>
            </div>
            <div>
              <label>Custom</label>
              <input name="column_size" type="text" v-model="grid_system">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button v-if="gridValidate" type="button" class="btn btn-primary" @click="addGrid" data-dismiss="modal">Add</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal ends -->
    <!-- Modal for adding modules -->
    <div class="modal fade" id="newmodule" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Select Module</h5>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal ends -->
  </div>
</div>
</template>

<script>
  import draggable from 'vuedraggable'
  export default {
    props: {
      grid: {
        type: Array,
        required: false,
        default: function () {
          return [];
        },
      },
      joptions: {
        type: Object,
        required: false,
        default: function () {
          return window.Joomla.getOptions('com_templates');
        },
      },
      images: {
        type: Object,
        required: false,
        default: function () {
          return this.joptions.images;
        },
      },
    },
    data() {
      return {
        myArray: [],
        element: '',
        key: 3,
        module_chrome: 'none',
        size_input: '3',
        edit_grid: false,
        edit_column: false,
        edit_position: false,
        selected_pos: '',
        grid_system: '',
        grid_selected: '',
        column_selected: '',
        column_size: '',
        grid_class: '',
        column_class: '',
        gridArray: this.grid,
        showSettings: false
      }
    },
    watch: {
      grid: {
        handler: function(newVal, oldVal) {
          document.getElementById('jform_params_grid').value = JSON.stringify(newVal);
        },
        deep: true,
      },
    },
    computed: {
      gridValidate() {
        var sum = 0;
        var gridSize = this.grid_system.split(" ");
        gridSize.forEach(element => {
          sum = sum + Number(element);
        });
        return (sum === 12);
      }
    },
    components: {
      draggable
    },
    methods: {
      addGrid() {
        var gridSize = this.grid_system.split(" ");
        this.myArray = [];
        gridSize.forEach(element => {
          this.myArray.push({
            type: 'column',
            options: {
              size: "col-sm-" + element
            },
            children: [{
              type: 'position',
              options: {},
              children: []
            }]
          })
        });
        this.gridArray.push({
          type: 'grid',
          options: {},
          children: this.myArray
        });
        this.grid_system = '';
      },
      deleteGrid(grid) {
        var index = this.gridArray.indexOf(grid);
        if(index > -1)
          this.gridArray.splice(index,1);
      },
      deleteColumn(grid,column) {
        var index = grid.children.indexOf(column);
        if(index > -1){
          grid.children.splice(index,1);
        }
      },
      editColumn(grid,column,submit) {
        if(submit){
          if(this.column_class != ''){
            var index = this.grid_selected.children.indexOf(this.column_selected);
            if(index > -1){
              this.grid_selected.children[index].options.class = this.column_class;
            }
          }
          this.back();
          this.column_class = '';
          this.grid_selected = '';
          this.column_selected = '';
        }
        else{
          this.edit_column = true;
          this.showSettings = true;
          this.column_selected = column;
          this.grid_selected = grid;
        }
      },
      editGrid(grid,submit) {
        if(submit){
          if(this.grid_class != ''){
            this.grid_selected.options.class = this.grid_class;
          }
          
          if(this.column_size != ''){
            this.grid_selected.children.push({
              type: 'column',
              options: {
                size: "col-sm-" + this.column_size
              },
              children: [{
                type: 'position',
                options: {},
                children: []
              }]
            });
          }

          this.grid_selected = '';
          this.column_size = '';
          this.grid_class = '';
          this.back();
        }
        else{
          this.edit_grid = true;
          this.grid_selected = grid;
          this.showSettings = true;
        }
      },
      back() {
        this.edit_grid = false;
        this.edit_column = false;
        this.edit_position = false;
        this.showSettings = false;
      },
      log(el) {
        console.log(el);
      }
    }
  }
</script>
