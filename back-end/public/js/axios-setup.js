// Pastikan axios sudah diimport di layout via CDN atau bundler
axios.defaults.withCredentials = true;
axios.defaults.headers.common['Accept'] = 'application/json';

// Inject CSRF token dari meta
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}
