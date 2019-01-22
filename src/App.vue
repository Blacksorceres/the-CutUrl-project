<template>
  <v-app dark>
    <v-toolbar app>
      <v-toolbar-title class="headline">
        <a href="https://cuturl.it" style="color:#fff;text-decoration:none;">
          <span>The Cuturl project</span>
        </a>
      </v-toolbar-title>
      <v-spacer></v-spacer>
    </v-toolbar>
    <v-content>
      <router-view></router-view>
    </v-content>
    <updateSnack />
  </v-app>
</template>

<script>
import router from './router.js';
import updateSnack from './components/SnackbarSwUpdated';

export default {
  name: 'App',
  router: router,
  components: {
    updateSnack
  },
  data () {
    return {
      //
    }
  }
}
</script>
<style>
@import './assets/css/class-rotate.css';
@import './assets/css/class-spinner.css';
@import './assets/css/master.css';
@import './assets/css/vue.css';
</style>
