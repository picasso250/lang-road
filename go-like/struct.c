#define STMT_TYPE_IMPORT 1
#define STMT_TYPE_FUNC_DEF 2
#define STMT_TYPE_VAR_DECL 3
#define STMT_TYPE_STRUCT_DECL 4
#define STMT_TYPE_ASSIGN 5
#define STMT_TYPE_CONST 6
#define STMT_TYPE_RETURN 7
#define STMT_TYPE_IF 8
#define STMT_TYPE_FOR_CLASSIC 9
#define STMT_TYPE_FOR_RANGE 10
#define STMT_TYPE_FOR_SWITCH 11
#define STMT_TYPE_EXPR 12
#define EXPR_TYPE_LITERAL 13
#define EXPR_TYPE_LIST 14
#define EXPR_TYPE_MAP 15
#define EXPR_TYPE_CALL 16
#define EXPR_TYPE_ARRAY_DEREF 17
#define EXPR_TYPE_POINTER_DEREF 18
#define EXPR_TYPE_POINTER_REF 19
#define EXPR_TYPE_MEMBER 20
#define EXPR_TYPE_ALLOC_STRUCT 21
#define EXPR_TYPE_READ_CHAN 22
#define EXPR_TYPE_SEND_CHAN 23
#define TYPE_DECL_TYPE_VAR 24
#define TYPE_DECL_TYPE_ARRAY 25
#define TYPE_DECL_TYPE_SLICE 26
#define TYPE_DECL_TYPE_MAP 27
#define TYPE_DECL_TYPE_POINTER 28
#define TYPE_DECL_TYPE_FUNC 29
#define LEFT_VAL_TYPE_NAME 30
#define LEFT_VAL_TYPE_DEREF 31
#define LEFT_VAL_TYPE_MEMBER 32
#define LITERAL_TYPE_INT 33
#define LITERAL_TYPE_FLOAT 34
#define LITERAL_TYPE_STRING 35
#define CALLEE_TYPE_NAME 36
#define CALLEE_TYPE_CLOSURE 37
struct file{char *_package;struct {int size; char**data;} _stmt_list;};
struct stmt{int type;
	union{
	struct {struct {int size; char**data;} _name_list;}_import;
	struct {struct reciever_decl *_reciever_decl;char *_name;struct params *_params;struct {int size; char**data;} _body;}_func_def;
	struct {char *_name;struct type_decl *_type_decl;}_var_decl;
	struct {struct {int size; char**data;} _stmt_var_decl;char *_is_interface;char *_embedded_from;}_struct_decl;
	struct {struct {int size; char**data;} _left_val_list;struct {int size; char**data;} _expr_list;}_assign;
	struct {struct left_val *_left_val;struct literal *_literal;}_const;
	struct {struct expr *_expr;}_return;
	struct {struct cond *_cond;struct {int size; char**data;} _then;struct {int size; char**data;} _else;}_if;
	struct {char *_init;struct cond *_cond;char *_post;struct {int size; char**data;} _body;}_for_classic;
	struct {struct {int size; char**data;} _name_list;char *_range;struct {int size; char**data;} _body;}_for_range;
	struct {struct cond *_cond;struct switch_case *_switch_case;struct {int size; char**data;} _default;}_for_switch;
	struct {struct expr *_expr;}_expr;
	};};
struct closure{struct params *_params;struct {int size; char**data;} _body;};
struct expr{int type;
	union{
	struct {struct literal *_literal;}_literal;
	struct {struct {int size; char**data;} _expr_list;}_list;
	struct {struct {int size; char**data;} _kv_list;}_map;
	struct {char *_reciever;struct callee *_callee;struct {int size; char**data;} _params;char *_variadic_name;char *_is_coroutine;}_call;
	struct {struct expr *_expr;char *_index;}_array_deref;
	struct {struct expr *_expr;}_pointer_deref;
	struct {struct expr *_expr;}_pointer_ref;
	struct {struct expr *_expr;char *_member;}_member;
	struct {char *_struct_type;struct struct_init_map *_struct_init_map;}_alloc_struct;
	struct {char *_chan;struct left_val *_left_val;}_read_chan;
	struct {char *_chan;char *_value;}_send_chan;
	};};
struct type_decl{int type;char *_is_chan;
	union{
	struct {char *_name;}_var;
	struct {char *_name;char *_len;}_array;
	struct {char *_name;}_slice;
	struct {char *_k_type;char *_v_type;}_map;
	struct {char *_name;}_pointer;
	struct {char *_reciever;struct {int size; char**data;} _params;char *_return;}_func;
	};};
struct reciever_decl{int type;char *_name;};
struct params{bool _has_variadic;struct {int size; char**data;} _name_list;};
struct left_val{int type;
	union{
	struct {char *_name;}_name;
	struct {char *_expr_deref;}_deref;
	struct {char *_expr_member;}_member;
	};};
struct cond{char *_stmt_assign;struct expr *_expr;};
struct switch_case{struct {int size; char**data;} _case;struct {int size; char**data;} _stmt_list;};
struct literal{int type;
	union{
	struct {int _int;}_int;
	struct {float _float;}_float;
	struct {char *_string;}_string;
	};};
struct kv{char *_key;char *_value;};
struct struct_init_map{struct {int size; char**data;} _kv_list;};
struct callee{int type;
	union{
	struct {char *_name;}_name;
	struct {struct closure *_closure;}_closure;
	};};
