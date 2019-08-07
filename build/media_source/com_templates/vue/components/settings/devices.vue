<template>
	<ul class="nav nav-pills">
    <li class="nav-item">
      <p class="nav-link" role="tab">Select Device</p>
    </li>
		<li class="nav-item" @click="setDevice('xs')">
			<a class="nav-link" data-toggle="pill" role="tab"><i class="fas fa-mobile-alt fa-lg"></i></a>
		</li>
		<li class="nav-item" @click="setDevice('sm')">
			<a class="nav-link" data-toggle="pill" role="tab"><i class="fas fa-tablet-alt fa-lg"></i></a>
		</li>
		<li class="nav-item" @click="setDevice('md')">
			<a class="nav-link" data-toggle="pill" role="tab"><i class="fas fa-laptop fa-lg"></i></a>
		</li>
		<li class="nav-item" @click="setDevice('lg')">
			<a class="nav-link active" data-toggle="pill" role="tab"><i class="fas fa-desktop fa-lg"></i></a>
		</li>
	</ul>
</template>

<script>
  import {mapMutations, mapState} from 'vuex';

  export default {
    name: 'devices',
    computed: {
      ...mapState([
        'resolution'
      ]),
    },
    methods: {
      ...mapMutations([
        'updateDeviceWidth'
      ]),
      setDevice(size) {
        this.updateDeviceWidth(size);
      }
    }
  };
</script>
