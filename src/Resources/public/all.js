const addCollectionItem = function (event, target) {
  const collectionHolder = document.getElementById(target)
  const collectionItem = collectionHolder.querySelectorAll('.collection-item')

  const { prototype } = event.currentTarget.dataset
  const newItem = prototype.replace(/__name__/g, collectionItem.length ++)

  collectionHolder.insertAdjacentHTML('beforeend', newItem.trim())
}

const removeCollectionItem = function (target) {
  if (false === confirm('Are you sure?')) return false
  const entry = document.getElementById(target)
  entry && entry.remove()
}
