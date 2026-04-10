import { VueRenderer } from '@tiptap/vue-3'
import tippy from 'tippy.js'

import List from './List.vue'

export default {
  items: async ({ editor, query }) => {
    const response = await axios.get(
      route('mixpost.resources.users', { workspace: editor.options.editorProps.workspaceId }),
      {
        params: {
          keyword: query
        }
      }
    )

    return response.data.data.map(user => ({
      id: user.id,
      name: user.name
    }))
  },
  render: () => {
    let component
    let popup

    return {
      onStart: props => {
        component = new VueRenderer(List, {
          // using vue 2:
          // parent: this,
          // propsData: props,
          // using vue 3:
          props,
          editor: props.editor
        })

        if (!props.clientRect) {
          return
        }

        popup = tippy('body', {
          getReferenceClientRect: props.clientRect,
          appendTo: () => document.body,
          content: component.element,
          showOnCreate: true,
          interactive: true,
          trigger: 'manual',
          placement: 'bottom-start'
        })
      },

      onUpdate(props) {
        component.updateProps(props)

        if (!props.clientRect) {
          return
        }

        popup[0].setProps({
          getReferenceClientRect: props.clientRect
        })
      },

      onKeyDown(props) {
        if (props.event.key === 'Escape') {
          popup[0].hide()

          return true
        }

        return component.ref?.onKeyDown(props)
      },

      onExit() {
        if (popup && popup[0]) {
          popup[0].destroy()
        }

        if (component) {
          component.destroy()
        }
      }
    }
  }
}
