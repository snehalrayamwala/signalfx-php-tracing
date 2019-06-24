#ifndef DD_ENV_CONFIG_H
#define DD_ENV_CONFIG_H
#include <stdint.h>

#if ZTS
#include <TSRM.h>
#else
#define TSRMLS_DC
#endif

#define BOOL_T uint8_t
#define TRUE (1)
#define FALSE (0)

BOOL_T ddtrace_get_bool_config(char *name, BOOL_T def TSRMLS_DC);
char *ddtrace_get_c_string_config(char *name TSRMLS_DC);
int64_t ddtrace_get_int_config(char *name, int64_t def TSRMLS_DC);
uint32_t ddtrace_get_uint32_config(char *name, uint32_t def TSRMLS_DC);
char *ddtrace_get_c_string_config_with_default(char *name, const char *def TSRMLS_DC);
char *ddtrace_strdup(const char *c);

#endif  // DD_ENV_CONFIG_H
