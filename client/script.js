// =======================
// 🔧 Konfigurasi dasar
// =======================
const BASE_URL = "http://192.168.56.2/uas-rest/server/server.php/"; // tetap pakai slash di akhir
// const BASE_URL = "http://host.docker.internal/uas-rest/server/server.php/";

// Utility: buat URL yang aman dari double slash
function buildUrl(endpoint) {
  if (!endpoint.startsWith("/")) return BASE_URL + endpoint;
  return BASE_URL + endpoint.substring(1);
}

// =======================
// 👤 Fungsi umum: Auth
// =======================
function getUser() {
  const user = localStorage.getItem("user");
  return user ? JSON.parse(user) : null;
}

function logout() {
  localStorage.removeItem("user");
  window.location.href = "login.html";
}

// =======================
// 📥 Fungsi GET (ambil data)
// =======================
async function getData(endpoint) {
  try {
    const response = await fetch(buildUrl(endpoint), {
      method: "GET",
      headers: { "Content-Type": "application/json" },
    });

    if (!response.ok) {
      console.error("HTTP Error:", response.status, response.statusText);
      return [];
    }

    const data = await response.json();
    console.log("✅ GET", endpoint, data);
    return data;
  } catch (error) {
    console.error("❌ Gagal GET data:", error);
    return [];
  }
}

// =======================
// ➕ Fungsi POST (tambah data)
// =======================
async function postData(endpoint, data) {
  try {
    const response = await fetch(buildUrl(endpoint), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });

    const result = await response.json();
    console.log("✅ POST", endpoint, result);
    return result;
  } catch (error) {
    console.error("❌ Gagal POST data:", error);
    return { error: error.message };
  }
}

// =======================
// ✏️ Fungsi PUT (update data)
// =======================
async function putData(endpoint, data) {
  try {
    const response = await fetch(buildUrl(endpoint), {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });

    if (!response.ok) {
      console.error("HTTP PUT Error:", response.status, response.statusText);
      return { error: `HTTP ${response.status}` };
    }

    const result = await response.json();
    console.log("✅ PUT", endpoint, result);
    return result;
  } catch (error) {
    console.error("❌ Gagal PUT data:", error);
    return { error: error.message };
  }
}

// =======================
// 🗑️ Fungsi DELETE (hapus data)
// =======================
// async function deleteData(endpoint) {
//   try {
//     const response = await fetch(buildUrl(endpoint), {
//       method: "DELETE",
//       headers: { "Content-Type": "application/json" },
//     });

//     if (!response.ok) {
//       console.error("HTTP DELETE Error:", response.status, response.statusText);
//       return { error: `HTTP ${response.status}` };
//     }

//     const result = await response.json();
//     console.log("✅ DELETE", endpoint, result);
//     return result;
//   } catch (error) {
//     console.error("❌ Gagal DELETE data:", error);
//     return { error: error.message };
//   }
// }
async function deleteData(endpoint) {
  try {
    const response = await fetch(BASE_URL + endpoint, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
    });
    const result = await response.json();
    console.log("DELETE", endpoint, result); // buat debug
    return result;
  } catch (error) {
    console.error("Gagal DELETE data:", error);
    return { error: error.message };
  }
}
