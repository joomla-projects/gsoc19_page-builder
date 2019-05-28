<template>
  <div class="raw">
    <h3>{{ title }}</h3>
    <pre>{{ valueString }}</pre>
  </div>
</template>

<script>
const props = {
  name: "raw-displayer",
  title: {
    required: false,
    type: String
  },
  value: {
    required: true
  }
};

export default {
  props,
  computed: {
    valueString() {
      return JSON.stringify(this.value, null, 2);
    }
  }
};
</script>
