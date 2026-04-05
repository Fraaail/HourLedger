#include "php_embed.h"
#include "PHP.h"

static phpOutputCallback swiftOutputCallback = NULL;

size_t capture_php_output(const char *str, size_t str_length) {
    // Forward to Swift callback if available
    if (swiftOutputCallback) {
        // We need a null-terminated C-string for Swift
        char *buffer = malloc(str_length + 1);
        memcpy(buffer, str, str_length);
        buffer[str_length] = '\0';
        swiftOutputCallback(buffer);
        free(buffer);
    }
    return str_length;
}

void override_embed_module_output(phpOutputCallback callback) {
    swiftOutputCallback = callback;
    php_embed_module.ub_write = capture_php_output;
}

void initialize_php_with_request(const char *post_data,
                                 const char *method,
                                 const char *uri) {

    // Initialize $_POST
    if (strcmp(method, "POST") == 0) {
        size_t post_data_length = strlen(post_data);

        php_stream *mem_stream = php_stream_memory_create(TEMP_STREAM_DEFAULT);
        php_stream_write(mem_stream, post_data, post_data_length);

        // Populate the php://input stream
        SG(request_info).request_body    = mem_stream;
        SG(request_info).request_method  = "POST";

        // TODO: Pass this in from outside
        SG(request_info).content_type    = "application/x-www-form-urlencoded";
        SG(request_info).content_length  = post_data_length;
    }
}
