<template>
	<div :class="['item', 'pagebuilder_' + element.type, element.options.class]" :id="element.type + '-' + element.key">
    
    <div class="btn-wrapper btn-group left">
      <i class="btn btn-primary btn-sm fa fa-align-justify handle"></i>
    </div>

		<div class="btn-wrapper btn-group right">
			<button type="button" class="btn btn-primary btn-sm" @click="$emit('edit')">
				<span class="icon-options"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT') }}</span>
			</button>
			<button type="button" class="btn btn-danger btn-sm" @click="$emit('delete')">
				<span class="icon-cancel"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_ELEMENT') }}</span>
			</button>
		</div>

		<div class="item-content">
			<div class="desc">
				<span>{{ element.title }}</span>
				<span v-if="element.options.class">.{{ element.options.class }}</span>
        <span v-if="element.options.name">(Name-{{ element.options.name }})</span>
			</div>

			<grid v-if="element.type === 'grid'" :grid="element"></grid>

			<div v-else>
				<item v-for="child in element.children" :key="child.key" :item="child" @delete="deleteElement({element: child, parent: item})" @edit="editElement({element: child, parent: item})"></item>

				<button v-if="childAllowed.includes(element.type)"
						type="button"
						class="btn btn-sm btn-success btn-add"
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
        'deleteElement',
        'fillAllowedChildren'
      ]),
      addElement(parent) {
        this.$store.commit('setParent', parent.children);
        this.$modal.show('add-element');
      },
    },
  };
</script>
