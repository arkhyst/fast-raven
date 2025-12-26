class Lib {
    /**
     * Send a request to an API endpoint.
     * @param {string} api - URL of the API endpoint.
     * @param {string} method - HTTP method to use (e.g. GET, POST, PUT, DELETE).
     * @param {Object} [data] - Optional data to send with the request.
     * @returns {Promise} A promise that resolves with the response from the API endpoint.
     */
    static request(api, method, data = undefined) {
        return new Promise(function(resolve, reject){
            $.ajax({
                url: api,
                method: method,
                data: JSON.stringify(data) ?? data,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN ?? ''
                },
                success: function(response){
                    resolve(response);
                },
                error: function(xhr, status, error){
                    reject(error);
                }
            });
        });
    }

    /**
     * Upload file(s) to an API endpoint using FormData.
     * @param {string} api - URL of the API endpoint.
     * @param {File|FileList|HTMLInputElement} file - File(s) to upload or input element.
     * @param {string} [fieldName] - Field name for backend access (default: 'file' or 'files[]').
     * @param {Object} [extraData] - Optional additional form data.
     * @returns {Promise} A promise that resolves with the response from the API endpoint.
     */
    static uploadFile(api, file, fieldName = null, extraData = {}) {
        return new Promise(function(resolve, reject) {
            const formData = new FormData();
            
            // Handle different input types
            if (file instanceof HTMLInputElement) {
                file = file.files;
            }
            
            if (file instanceof FileList) {
                const name = fieldName || 'files[]';
                for (let i = 0; i < file.length; i++) {
                    formData.append(name, file[i]);
                }
            } else if (file instanceof File) {
                formData.append(fieldName || 'file', file);
            }
            
            // Add CSRF token for POST validation
            formData.append('csrf_token', window.CSRF_TOKEN ?? '');
            
            // Add extra data
            for (const [key, value] of Object.entries(extraData)) {
                formData.append(key, value);
            }
            
            $.ajax({
                url: api,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    resolve(response);
                },
                error: function(xhr, status, error) {
                    reject(error);
                }
            });
        });
    }
}

for(const req of XXX_PHP_AUTOFILL) {
    Lib.request(req["api"], "GET").then(data => {
        const result = data.data;
        if(result == null || result.length == 0) return;
        
        $(req["dom"]).html(result.toString());
    });
} 
