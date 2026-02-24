const DB_NAME = "systex_ponto_offline";
const DB_VER = 1;
const STORE = "pending_punches";

// ✅ Ajuste: base do seu projeto em produção
const BASE_PATH = "/systex-ponto/public";
const SYNC_URL = `${BASE_PATH}/ponto/sync-offline`;

function openDB() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(DB_NAME, DB_VER);
    req.onupgradeneeded = () => {
      const db = req.result;
      if (!db.objectStoreNames.contains(STORE)) {
        db.createObjectStore(STORE, { keyPath: "uuid" });
      }
    };
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}

async function putPending(item) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE, "readwrite");
    tx.objectStore(STORE).put(item);
    tx.oncomplete = () => resolve(true);
    tx.onerror = () => reject(tx.error);
  });
}

async function getAllPending() {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE, "readonly");
    const req = tx.objectStore(STORE).getAll();
    req.onsuccess = () => resolve(req.result || []);
    req.onerror = () => reject(req.error);
  });
}

async function deletePending(uuid) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(STORE, "readwrite");
    tx.objectStore(STORE).delete(uuid);
    tx.oncomplete = () => resolve(true);
    tx.onerror = () => reject(tx.error);
  });
}

function uuidv4() {
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, c => {
    const r = crypto.getRandomValues(new Uint8Array(1))[0] & 15;
    const v = c === "x" ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

async function syncNow() {
  if (!navigator.onLine) return { ok: false, reason: "offline" };

  const pendings = await getAllPending();
  if (!pendings.length) return { ok: true, results: [], message: "no_pending" };

  const fd = new FormData();
  fd.append("items", JSON.stringify(pendings));

  const res = await fetch(SYNC_URL, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      "X-CSRF-TOKEN": window.csrfToken || "",
      "Accept": "application/json"
    },
    body: fd
  });

  // Se Laravel devolver HTML de erro, isso aqui ajuda muito
  const text = await res.text();
  let json;
  try { json = JSON.parse(text); } catch(e) { json = { raw: text }; }

  if (!res.ok) {
    // joga o erro real pra tela (419/422/500 etc)
    const err = new Error(`Sync HTTP ${res.status}`);
    err.status = res.status;
    err.payload = json;
    throw err;
  }

  // limpa da fila somente o que o servidor aceitou como salvo/duplicado
  for (const r of (json.results || [])) {
    if (r.status === "saved" || r.status === "duplicate_ignored") {
      await deletePending(r.uuid);
    }
  }

  return json;
}


window.SystexOffline = { uuidv4, putPending, syncNow };

window.addEventListener("online", () => syncNow());

// ✅ Quando voltar a internet, tenta sincronizar e atualiza o "Última ação"
window.addEventListener("online", async () => {
    debugSet("debugAcao", "Conexão voltou. Sincronizando...");
    try {
        const r = await window.SystexOffline.syncNow();

        const results = (r && r.results) ? r.results : [];
        const saved = results.filter(x => x.status === "saved").length;
        const dup = results.filter(x => x.status === "duplicate_ignored").length;
        const blocked = results.filter(x => x.status === "blocked_same_type").length;
        const invalid = results.filter(x => x.status === "invalid_item").length;
        const userNF = results.filter(x => x.status === "user_not_found").length;

        debugSet("debugAcao",
            `Sync OK ✅ (salvos: ${saved}, duplicados: ${dup}, bloqueados: ${blocked}, inválidos: ${invalid}, user_nf: ${userNF})`
        );

        console.log("SYNC RESULT:", r);

    } catch (e) {
        console.error("SYNC ERROR:", e);
        debugSet("debugAcao", `Falha no sync ❌ (HTTP ${e.status || "?"})`);
        alert("Falha no sync. Veja o console (F12) para detalhes.");
    }
});


// ✅ Ao abrir a página online, tenta sincronizar pendências automaticamente
document.addEventListener("DOMContentLoaded", async () => {
    if (navigator.onLine) {
        try {
            if (window.SystexOffline && window.SystexOffline.syncNow) {
                await window.SystexOffline.syncNow();
            }
        } catch (e) {}
    }
});

