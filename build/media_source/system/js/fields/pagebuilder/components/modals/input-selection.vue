<template>
	<div>
		<input  :type="type" :id="id" :name="id" class="form-control" required
				:placeholder="placeholder" v-model="content"  @click="toggleSelection" />
		<div id="inputselection_wrapper" v-if="show_list">
			<span @click="select_item(custom_pos)" class="inputselection_list" v-for="custom_pos in options">{{custom_pos}}</span>
		</div>

	</div>
</template>

<script>
    export default {
        name: "input-selection",
		props: ['placeholder', 'value', 'id', 'type', 'options'],
		data() {
          return {
              show_list: false,
			  content: this.value
		  }
		},
		methods: {
            select_item(payload){
                this.content = payload
				this.toggleSelection()
			},
            toggleSelection(){
                this.show_list = !this.show_list
			}
		},
        watch:{
            content(inew){
                this.$emit('input', inew)
			}

		}
    }
</script>

<style scoped>
	#inputselection_wrapper {
		max-width: 100%;
		overflow-y: scroll;
		overflow-x: hidden;
		height: 150px;
		border: 1px solid #cdcdcd;
	}
	.inputselection_list{
		display: block;
		padding: 8px 12px;
	}
	.inputselection_list:hover{
		color: #fff;
		background-color: #0d2538;
		border-color: #0d2538;
	}
</style>
