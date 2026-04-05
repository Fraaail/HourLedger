#ifndef PHPBridge_h
#define PHPBridge_h

typedef void (*phpOutputCallback)(const char *);

void override_embed_module_output(phpOutputCallback callback);

void initialize_php_with_request(const char *post_data,
                                 const char *method,
                                 const char *uri);

#endif
