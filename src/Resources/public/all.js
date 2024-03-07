const addCollectionEntry = (event, collectionId, replaceName) => {
  const collection = document.getElementById(collectionId)
  const { index, prototype } = event.currentTarget.dataset

  const newEntry = prototype.replace(new RegExp(replaceName, 'g'), index)
  collection.insertAdjacentHTML('beforeend', newEntry.trim())

  event.currentTarget.dataset.index ++
}

const removeCollectionEntry = (collectionEntryId, confirmationText) => {
  if (false === confirm(confirmationText)) return false
  const collectionEntry = document.getElementById(collectionEntryId)
  collectionEntry && collectionEntry.remove()
}
