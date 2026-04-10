import { Extension } from '@tiptap/core'
import { keymap } from 'prosemirror-keymap'

const PreventLinkDeletion = Extension.create({
  name: 'preventLinkDeletion',

  addProseMirrorPlugins() {
    return [
      keymap({
        Backspace: (state, dispatch) => {
          return handleDeleteSelection(state, dispatch)
        },
        Delete: (state, dispatch) => {
          return handleDeleteSelection(state, dispatch)
        }
      })
    ]
  }
})

const handleDeleteSelection = (state, dispatch) => {
  const { doc, tr, selection } = state
  const { from, to } = selection

  if (from === to) return false

  const deleteRanges = []

  doc.nodesBetween(from, to, (node, pos) => {
    if (!node.text) {
      return true
    }

    const nodeStart = pos
    const nodeEnd = pos + node.nodeSize

    const isFullySelected = nodeStart >= from && nodeEnd <= to

    const isProtected = node.marks?.some(
      mark => mark.type.name === 'link' && mark.attrs.class?.includes('non_editable')
    )

    if (isProtected && isFullySelected) {
      deleteRanges.push([nodeStart, nodeEnd])
    }

    if (!isProtected) {
      // Normal content (even if partially selected)
      const safeFrom = Math.max(nodeStart, from)
      const safeTo = Math.min(nodeEnd, to)

      if (safeFrom < safeTo) {
        deleteRanges.push([safeFrom, safeTo])
      }
    }
  })

  if (deleteRanges.length === 0) {
    return true
  } // let default deletion happen

  // Delete from last to first to avoid shifting
  deleteRanges
    .sort((a, b) => b[0] - a[0])
    .forEach(([start, end]) => {
      try {
        tr.delete(start, end)
      } catch (err) {
        console.warn('⚠️ Failed to delete:', start, end, err) // eslint-disable-line no-console
      }
    })

  if (dispatch) {
    dispatch(tr)
  }

  return true
}
export default PreventLinkDeletion
