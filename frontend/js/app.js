const API_URL = "/backend/api/items.php";

function setMessage(msg, ok=false) {
  const el = document.querySelector("#message");
  el.textContent = msg || "";
  el.style.color = ok ? "green" : "#b00020";
}

function renderItems(items) {
  const ul = document.querySelector("#items");
  ul.innerHTML = "";

  if (!items.length) {
    const li = document.createElement("li");
    li.textContent = "Brak elementów.";
    ul.appendChild(li);
    return;
  }

  for (const it of items) {
    const li = document.createElement("li");
    li.textContent = `${it.id}: ${it.title}`;
    ul.appendChild(li);
  }
}

async function loadItems() {
  const res = await fetch(API_URL);
  const items = await res.json();
  renderItems(items);
}

async function addItem(title) {
  const res = await fetch(API_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ title })
  });
  const payload = await res.json().catch(()=> ({}));
  if (!res.ok) throw new Error(payload.error || "Błąd POST");
}

document.addEventListener("DOMContentLoaded", () => {
  loadItems().catch(()=> setMessage("Nie udało się pobrać listy."));

  document.querySelector("#addForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    setMessage("");

    const input = document.querySelector("#title");
    const title = input.value.trim();

    if (title.length < 3) return setMessage("Min 3 znaki.");

    try {
      await addItem(title);
      input.value = "";
      setMessage("Dodano!", true);
      await loadItems();
    } catch (err) {
      setMessage(err.message);
    }
  });
});

