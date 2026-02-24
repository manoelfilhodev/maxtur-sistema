const CACHE_NAME = "systex-ponto-cache-v1";

// arquivos mínimos para abrir a tela offline
const FILES_TO_CACHE = [
  "/systex-ponto/public/",
  "/systex-ponto/public/offline.html",
];

// INSTALAÇÃO
self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(FILES_TO_CACHE);
    })
  );
  self.skipWaiting();
});

// ATIVAÇÃO
self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      )
    )
  );
  self.clients.claim();
});

// INTERCEPTA REQUISIÇÕES
self.addEventListener("fetch", event => {

  // Navegação (HTML)
  if (event.request.mode === "navigate") {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          const copy = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, copy);
          });
          return response;
        })
        .catch(() => {
          return caches.match("/systex-ponto/public/offline.html");
        })
    );
    return;
  }

  // Arquivos estáticos (JS, CSS, imagens)
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request);
    })
  );
});
