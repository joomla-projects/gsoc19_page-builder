<template>
	<div :class="['item', element.type]">
		<div class="btn-wrapper">
			<button type="button" class="btn btn-lg" @click="editElement(element)">
				<span class="icon-options"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT') }}</span>
			</button>
			<button type="button" class="btn btn-lg" @click="$emit('delete')">
				<span class="icon-cancel"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_ELEMENT') }}</span>
			</button>
		</div>

		<div class="item-content">
			<div class="desc">
				<span>{{ element.type }}</span>
				<span v-if="element.options.class">.{{ element.options.class }}</span>
			</div>

			<grid v-if="element.type === 'Grid'" :grid="element"></grid>

			<div v-else>
				<item v-for="child in element.children" :key="child.id" :item="child" @delete="deleteElement(child)"></item>

				<button v-if="childAllowed.includes(element.type)"
						type="button"
						class="btn btn-add btn-outline-info"
						@click="addElement(element)">
					<span class="icon-new"></span>
					<span class="sr-only">{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}</span>
				</button>
			</div>
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
        'editElement',
        'fillAllowedChildren'
      ]),
      addElement(parent) {
        this.fillAllowedChildren(parent.type);
        this.$store.commit('addElement', parent);
        this.$modal.show('add-element');
      },
      deleteElement(child) {
        this.$store.commit('deleteElement', {
          element: child,
          parent: this.item
        });
      },
    },
  };
</script>
