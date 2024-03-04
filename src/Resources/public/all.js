const addCollectionItem = (event, target) => {
  const collectionHolder = document.getElementById(target)
  const collectionItem = collectionHolder.querySelectorAll('.collection-item')

  const { prototype } = event.currentTarget.dataset
  const newItem = prototype.replace(/__name__/g, collectionItem.length ++)

  collectionHolder.insertAdjacentHTML('beforeend', newItem.trim())
}

const removeCollectionItem = (target, confirmationText) => {
  if (false === confirm(confirmationText)) return false
  const entry = document.getElementById(target)
  entry && entry.remove()
}
