<template>
<div class="container-fluid">
  <!--<div id="Settings" class="settings col-sm-2">
      <h2>Settings</h2>
      <hr>
      <form>
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

                <div class="form-group">
                  <label for="size_input">Select module size</label>
                  <input id="size_input" name="size_input" type="text" v-model="size_input" class="form-control">
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-success" @click="save">Save</button>
                    <button type="button" class="btn btn-secondary" @click="back">Back</button>
                </div>
            </fieldset>
        </div>

        <div v-else-if="edit_column" class="btn-group">
            <button type="button" class="btn btn-success" @click="">Save</button>
            <button type="button" class="btn btn-secondary" @click="">Back</button>
        </div>

      <div v-else-if="edit_grid" class="form-group">
        <fieldset>
            <legend>Edit Grid</legend>
            <label for="column_size">Add new column</label>
            <input id="column_size" name="column_size" type="text" v-model="column_size">

            <div class="btn-group">
                <button type="button" class="btn btn-success" @click="editGrid('',true)">Save</button>
                <button type="button" class="btn btn-danger" @click="back">Back</button>
            </div>
        </fieldset>
      </div>

      <div v-else>
          <fieldset>
            <legend>Add new grid</legend>
            <label for="grid_type">Select grid system(should add to 12)</label>
            <input id="grid_type" name="grid_type" type="text" v-model="grid_system">

            <div class="btn-group">
                <button type="button" class="btn btn-primary" @click="addGrid">Add</button>
                <button type="button" class="btn btn-secondary" @click="grid_system = ''">Reset</button>
            </div>
          </fieldset>
      </div>
    </form>
  </div>-->

  <div id="View">
    <h2>View</h2>
    <draggable v-model="gridArray">
      <div v-for="grid in gridArray" v-model="gridArray" class="draggable">
        <button v-if="gridArray.length" type="button" class="icon-cancel close" @click="deleteGrid(grid)"></button>
        <button v-if="gridArray.length" type="button" class="icon-apply close" @click="editGrid(grid,false)"></button>
        <draggable v-model="grid.children" class="row grid-row">
          <div class="list-group-item" v-for="column in grid.children" :class="[column.options.size]" @click="">
            {{column.options.size}}
            (<i>{{column.type}}</i>)
            <button type="button" class="icon-cancel close" @click="deleteColumn(grid,column)"></button>
            <button type="button" class="icon-apply close" @click="editGrid(grid,false)"></button>
          </div>
        </draggable>
      </div>
    </draggable>
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
              <div class="col-sm"><img src="./icons/row_12.png" height="25" width="25" @click="grid_system = '12'"></div>
              <div class="col-sm"><img src="./icons/row_6_6.png" height="25" width="25" @click="grid_system = '6 6'"></div>
              <div class="col-sm"><img src="./icons/row_4_8.png" height="25" width="25" @click="grid_system = '4 8'"></div>
              <div class="col-sm"><img src="./icons/row_8_4.png" height="25" width="25" @click="grid_system = '8 4'"></div>
              <div class="col-sm"><img src="./icons/row_3_3_3_3.png" height="25" width="25" @click="grid_system = '3 3 3 3'"></div>
              <div class="col-sm"><img src="./icons/row_4_4_4.png" height="25" width="25" @click="grid_system = '4 4 4'"></div>
              <div class="col-sm"><img src="./icons/row_3_6_3.png" height="25" width="25" @click="grid_system = '3 6 3'"></div>
            </div>
            <div>
              <label>Custom</label>
              <input name="column_size" type="text" v-model="grid_system">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" @click="addGrid" data-dismiss="modal">Add</button>
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
      }
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
        column_size: '',
        gridArray: this.grid,
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
      editColumn(element,grid) {
        this.edit_column = true;
      },
      editGrid(grid,submit) {
        if(submit){
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
          this.grid_selected = '';
          this.column_size = '';
          this.edit_grid = false;
        }
        else{
          this.edit_grid = true;
          this.grid_selected = grid;
        }
      },
      back() {
        this.edit_grid = false;
        this.edit_column = false;
        this.edit_position = false;
      },
      log(el) {
        console.log(el);
      }
    }
  }
</script>
