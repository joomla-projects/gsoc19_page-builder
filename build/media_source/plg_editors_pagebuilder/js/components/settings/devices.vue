<template>
	<ul id="devices" class="nav nav-pills">
		<li v-for="(icon, size) in deviceBtns" class="nav-item">
			<button @click="setDevice(size)" :id="size" class="btn btn-info" type="button">
				<span :class="'fas ' + icon + ' fa-lg'" aria-hidden="true"></span>
				<span class="sr-only">{{ translate(`PLG_PAGEBUILDER_DEVICE_${size.toUpperCase()}_SR_DESC`) }}</span>
			</button>
		</li>
		<li class="nav-item">
			<span id="device-desc" class="nav-link"></span>
		</li>
	</ul>
</template>

<script>
  import {mapMutations, mapState} from 'vuex';

  export default {
    name: 'devices',
    computed: {
      ...mapState([
        'activeDevice',
      ]),
      deviceDesc() {
        return document.getElementById('device-desc');
      },
    },
    data() {
      return {
        deviceBtns: {
          xs: 'fa-mobile-alt',
          sm: 'fa-tablet-alt',
          md: 'fa-tablet-alt fa-rotate-270',
          lg: 'fa-laptop',
          xl: 'fa-desktop',
        },
      };
    },
    mounted() {
      document.querySelector(`#devices #${this.activeDevice}`).classList.add('active');
      this.deviceDesc.innerText = this.translate(`PLG_PAGEBUILDER_DEVICE_${this.activeDevice.toUpperCase()}`);
    },
    methods: {
      ...mapMutations([
        'updateDeviceWidth',
      ]),
      setDevice(size) {
        this.updateDeviceWidth(size);

        const buttons = document.querySelectorAll('#devices button');
        buttons.forEach(btn => {
          if (btn.id === size) {
            btn.classList.add('active');
          } else {
            btn.classList.remove('active');
          }
        });

        this.deviceDesc.innerText = this.translate(`PLG_PAGEBUILDER_DEVICE_${size.toUpperCase()}`);
      },
    },
  };
</script>
