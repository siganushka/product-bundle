import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['container']

  addItem(event) {
    const { index, prototype, prototypeName } = event.currentTarget.dataset
    const entry = prototype.replace(new RegExp(prototypeName, 'g'), index)

    this.containerTarget.insertAdjacentHTML('beforeend', entry.trim())
    event.currentTarget.dataset.index ++
  }

  removeItem(event) {
    event.preventDefault()

    const { confirmText, nodeId } = event.currentTarget.dataset
    if (confirm(confirmText)) {
      const entry = document.getElementById(nodeId)
      entry && entry.remove()
    }
  }
}
