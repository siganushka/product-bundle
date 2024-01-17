const addCollectionItem = function (event, target) {
  const collectionHolder = document.getElementById(target)
  const collectionItem = collectionHolder.querySelectorAll('.collection-item')

  const { prototype } = event.currentTarget.dataset
  const newItem = prototype.replace(/__name__/g, collectionItem.length ++)
console.log('@' + newItem + '@')
  collectionHolder.insertAdjacentHTML('beforeend', newItem.trim())
}

const removeCollectionItem = function (target) {
  if (confirm('确定删除码？')) {
    const entry = document.getElementById(target)
    entry && entry.remove()
  }
}
