const addCollectionItem = function (event, target) {
  const collectionHolder = document.getElementById(target)
  const collectionItem = collectionHolder.querySelectorAll('.collection-item')

  const newItem = document.createElement('dev')
  newItem.innerHTML = event.currentTarget.dataset.prototype.replace(/__name__/g, collectionItem.length ++)

  collectionHolder.appendChild(newItem.firstElementChild)
}

const removeCollectionItem = function (target) {
  if (confirm('确定删除码？')) {
    const entry = document.getElementById(target)
    entry && entry.remove()
  }
}
