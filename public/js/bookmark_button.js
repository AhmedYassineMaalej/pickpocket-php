bookmarkButtons = document.querySelectorAll(".product-bookmark-btn");

bookmarkButtons.forEach((button) => {
  button.addEventListener("click", async (_event) => {
    // check to see if bookmark is already set
    if (button.classList.contains("bookmark-full")) {
      await removeBookmark(button.dataset.reference);
      button.parentElement.remove();
    } else {
      await addBookmark(button.dataset.reference);
    }

    button.classList.toggle("bookmark-full");
  });
});

async function addBookmark(productReference) {
  const data = new FormData();

  data.append("productReference", productReference);

  return await fetch("/bookmarks/add", {
    method: "POST",
    body: data,
  }).then((response) => response.ok);
}

async function removeBookmark(productReference) {
  const data = new FormData();

  data.append("productReference", productReference);
  await fetch("/bookmarks/remove", {
    method: "POST",
    body: data,
  });
}
