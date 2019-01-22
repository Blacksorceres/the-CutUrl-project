import Vue from 'vue'
import Router from 'vue-router'

import Home from './routes/Home.vue'
import Redirect from './routes/Redirect.vue'

Vue.use(Router)

export default new Router({
  mode: 'history',
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home
    },
    {
      path: '/:short',
      name: 'redirect',
      component: Redirect
    }
  ]
})
