import './bootstrap'
import '../css/app.css'
import 'floating-vue/dist/style.css'
import '@css/overrideTooltip.css'
import 'vue-select/dist/vue-select.css'
import '@css/overrideVSelect.css'

import { createApp, h } from 'vue'
import { createInertiaApp, router } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from 'ziggy-js'
import { vTooltip } from 'floating-vue'
import vSelect from 'vue-select'
import Confirmation from './Plugins/Confirmation/Confirmation'
import AuthPasswordConfirmation from './Plugins/AuthPasswordConfirmation/AuthPasswordConfirmation'
import PanelLayout from '@/Layouts/Panel.vue'
import { i18n } from '@/Services/i18n.js'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Mixpost'

createInertiaApp({
  title: title => `${title} - ${appName}`,
  resolve: name => {
    const page = resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'))

    page.then(module => {
      module.default.layout = module.default.layout || PanelLayout
    })

    return page
  },
  setup({ el, App, props, plugin }) {
    // eslint-disable-next-line vue/component-api-style
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .directive('tooltip', vTooltip)
      .use(ZiggyVue)
      .use(Confirmation)
      .use(AuthPasswordConfirmation)
      .use(i18n)
      .provide('routePrefix', 'mixpost_e')
      .provide('routeProPrefix', 'mixpost')
      .component('v-select', vSelect)
      .mount(el)
  },
  progress: {
    color: '#4F46BB'
  }
})

// Refresh page on history operation
let stale = false

window.addEventListener('popstate', () => {
  stale = true
})

router.on('navigate', event => {
  const page = event.detail.page

  if (stale) {
    router.get(page.url, {}, { replace: true, preserveScroll: true, preserveState: false })
  }

  stale = false
})
