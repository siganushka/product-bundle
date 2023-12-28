const addCollectionItem = function (event, target) {
  const collectionHolder = document.getElementById(target)
  const item = document.createElement('dev')
  item.innerHTML = event.currentTarget.dataset.prototype.replace(/__name__/g, collectionHolder.children.length ++)

  collectionHolder.appendChild(item.firstChild)
}

const removeCollectionItem = function (target) {
  if (confirm('确定删除码？')) {
    const entry = document.getElementById(target)
    entry && entry.remove()
  }
}
