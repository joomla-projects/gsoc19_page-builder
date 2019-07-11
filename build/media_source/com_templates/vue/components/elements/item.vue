<template>
	<div :class="['item', element.type]">
		<span class="desc">{{ element.type }}
			<span v-if="element.options.class">.{{ element.options.class }}</span>
		</span>

		<div class="btn-wrapper">
			<button type="button" class="btn btn-lg" @click="editElement(element)">
				<span class="icon-options"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT') }}</span>
			</button>
			<button type="button" class="btn btn-lg" @click="deleteElement(element)">
				<span class="icon-cancel"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_ELEMENT') }}</span>
			</button>
		</div>

		<grid v-if="element.type === 'Grid'" :grid="element"></grid>

		<div v-if="element.type !== 'Grid'">
			<div v-for="child in element.children">
				<item :item="child"></item>
			</div>

			<button v-if="childAllowed.includes(element.type)" type="button" class="btn btn-add btn-outline-info"
					@click="addElement(element)">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
			</button>
		</div>
	</div>
</template>

<script>
  import {mapMutations, mapState} from 'vuex';

  export default {
    name: 'item',
    props: {
      item: {
        required: true,
        type: Object,
      },
    },
    computed: {
      ...mapState([
        'childAllowed',
      ]),
    },
    data() {
      return {
        element: this.item
      };
    },
    methods: {
      ...mapMutations([
        'deleteElement',
        'editElement',
        'fillAllowedChildren'
      ]),
      addElement(parent) {
        this.fillAllowedChildren(parent.type);
        this.$store.commit('addElement', parent);
        this.$modal.show('add-element');
      },
    },
  };
</script>
