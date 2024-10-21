import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['container']

  static values = {
    index: Number,
    prototype: String,
    prototypeName: String,
    confirm: String,
  }

  addItem() {
    const { indexValue, prototypeValue, prototypeNameValue } = this
    const entry = prototypeValue.replace(new RegExp(prototypeNameValue, 'g'), indexValue)

    this.containerTarget.insertAdjacentHTML('beforeend', entry.trim())
    this.indexValue ++
  }

  removeItem(event) {
    event.preventDefault()
    if (confirm(this.confirmValue)) {
      const { node } = event.currentTarget.dataset
      const entry = document.getElementById(node)
      entry && entry.remove()
    }
  }
}
