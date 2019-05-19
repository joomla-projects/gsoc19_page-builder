<template>
  <div class="container">
    <div class="row">Page Builder</div>
    <hr>

    <div class="form-group">
      <button class="btn btn-primary" @click="add">Add</button>
      <button class="btn btn-primary" @click="remove">Remove</button>
    </div>

    <draggable v-model="myArray">
      <div class="list-group-item" v-for="element in myArray" :key="key">{{element}}</div>
    </draggable>

    <rawDisplayer class="col-3" :value="myArray" title="List" />

  </div>
</template>

<script>
  import draggable from 'vuedraggable'
  import rawDisplayer from './components/raw-displayer'

  export default {
    data() {
      return {
        myArray: ['pos-1','pos-2','pos-3'],
        element: '',
        key: 3
      }
    },
    components: {
      draggable,
      rawDisplayer
    },
    methods: {
      add() {
        this.key++;
        this.myArray.push("pos-" + this.key);
      },
      remove() {
        this.myArray.pop();
        this.key--;
      }
    },
  }
</script>

<style>
  button {
    margin: 0.1em
  }
</style>
