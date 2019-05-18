<template>
  <div class="container">
    <div class="row">Page Builder</div>
    <hr>

    <div class="form-group">
      <button class="btn btn-primary">Add</button>
      <button class="btn btn-primary">Remove</button>
    </div>

    <draggable :list="list"></draggable>

  </div>
</template>

<script>
  

  export default {

  }
</script>

<style>
  button {
    margin: 0.1em
  }
</style>
