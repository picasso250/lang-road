// prepare for struct json
enum JsonType {
	object = 1;
	array = 2;
	string = 3;
	number = 4;
	true_ = 5;
	false_ = 6;
	null_ = 7;
	int_ = 8;
	float_ = 9;
}; 
typedef struct _json_object {
	int size;
	json_key_value *kv;
} json_object;
typedef struct _json_key_value {
	struct _json_string key;
	struct _json value;
} json_key_value;
typedef struct _json_array {
	int size;
	struct _json;
} json_array;
typedef struct _json_string {
	int size;
	char *str;
} json_string;
typedef struct _json_number {
	int type;
	union {
		long i;
		double f;
	};
} json_number;

typedef struct _json {
	int type;
	union {
		json_object o;
		json_array a;
		json_string s;
		json_number n;
	};
} json;

// methods

json *json_parse(char * str)
{
	return null;
}
char *json_stringify(json *j)
{
	return null;
}
json_object_get(json_object o, char *key)
{
	return null;
}
json_object_set(json_object o, char *key, json *value);
json_array_get(json_array a, int i);
json_array_set(json_array a, int i, json value);
json_array_push(json_array a, json value);
