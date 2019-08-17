# crud-stub
 Stubs for CRUD operations (Examples are Laravel-based)

# Instalation

# Replacing keys
- {{Model}} = Model
- {{models}} = models
- {{model}} = model

# MVC Concept summary

## Controller.stub
### index
```php
public function index(Request $request) {

    /* Concat ',ghost' to $request->mods (?mods=) */
    $request->replace(['mods' => $request->input('mods').',ghost']);

    /* Get all records available in the database.
    Can be filtered with where()
    ex: {{Model}}::where('user_id', Auth::user()->id)->get(); */
    ${{models}} = {{Model}}::all();
    
    /* Loop through records assigning its - show() rendered - views to an array */
    foreach (${{models}} as ${{model}}) {
        $views[] = $this->show($produto, $request);
    }

    return view('{{Model}}.index') // Return new index view
            ->with('{{models}}', ${{models}}) // with the records
            ->with('{{model}}_novo', new {{Model}}()) // a new instance for user insertion
            ->with('views', $views); // the views array
}
```
### show
```php
public function show(Produto $produto, Request $request) {

    $mods = $request->has('mods') ? explode(',', $request->input('mods')) : []; // Gets all request modifiers
    foreach($mods as $modifier){$mods[$modifier] = 1;} // Format to apply them in dinamic view
    
    return view('Produto.show')->with('produto', $produto)->with($mods); // Return view to index() array
}
```
### store
```php
public function store(Request $request, {{Model}} ${{model}}) {
    
    ${{model}}->fill($request->{{model}});
    ${{model}}->save();
    return $this->index();
}
```

### update
```php
public function update(Request $request, {{Model}} ${{model}}) {

    ${{model}}->update($request->{{model}});
    return redirect()->route('{{models}}.index');
}
```
### destroy
```php
public function destroy({{Model}} ${{model}}) {

    ${{model}}->delete();
    return redirect()->route('{{models}}.index');
}
```

## Model.stub
Extends [Model2.php](#)
```php
namespace App;

class {{Model}} extends Model2 {

    protected $guarded = ['id'];
    protected $fillable = [];
    public $editable = []; // ['field' => 'Formated Field Label',...]
}
```

## ViewIndex.stub
```blade
@extends('layouts.app')

@section('content')

@isset($alert)
    <div class="alert alert-{{$alert[0]}} mt-1" role="alert">
        {{$alert[1]}}
    </div>
@endisset

@if(empty(${{models}}))
    <div class="w-100 h-75 center-flex">
        <h2>Nenhum(a) {{model}} cadastrado(a)</h2>
    </div>
@endif

<!-- Loop through view array inserting the HTMLs -->
<div class="card-deck">
    @foreach (${{models}} as ${{model}})
        {!!${{model}}!!}
    @endforeach
</div>

@if (count(${{model}}_novo->editable))
    <button class="btn btn-primary btn-action" data-toggle="modal" data-target="#{{model}}-new">
        <i class="fas fa-plus"></i>
    </button>

    <form action="{{route('{{models}}.store')}}" method="POST" class="m-0">
        @csrf
        <div class="modal" id="{{model}}-new" tabindex="-1" role="dialog">

            <div class="modal-dialog" role="document">

                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Novo {{model}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">{!!${{model}}->getEditHTML()!!}</div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endif

@endsection
```

## ViewShow.stub
```blade
<div class="card" style="max-width: 300px">

    <!-- CR[UD] controls -->
    <form action="{{route('{{models}}.destroy', ['{{model}}' => ${{model}}->getKey()])}}" class="m-0" method="POST">
        @csrf

        @method('DELETE')
        <button class="btn btn-danger rounded-0">
            <i class="fas fa-trash-alt"></i>
        </button>

        <button type="button" data-toggle="modal" data-target="#{{model}}-{{${{model}}->getKey()}}" class="btn btn-primary rounded-0">
            <i class="fas fa-edit"></i>
        </button>
    </form>

    <img src="{{${{model}}->imagem}}" class="card-img-top" width="200" height="200">

    <div class="card-body">
        <h5 class="card-title">{{${{model}}->nome}}</h5>
        <p class="card-text">{{${{model}}->descricao}}</p>
    </div>

    <div class="card-footer d-none d-lg-block"></div>

    <!-- CR[U]D form -->
    <form action="{{route('{{models}}.update', ['{{model}}' => ${{model}}->getKey()])}}" method="post">
                        
        @csrf
        @method('PUT')

        <div class="modal" id="{{model}}-{{${{model}}->getKey()}}" tabindex="-1" role="dialog">

            <div class="modal-dialog" role="document">

                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">{{${{model}}->nome}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">{!!${{model}}->getEditHTML()!!}</div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
```

## Model2
### getColumnsType
```php
public function getColumnsType(){

    $fields = \DB::select(\DB::raw('SHOW COLUMNS FROM '.$this->table));
    $types = [];
    foreach ($fields as $i => $info) {
        $type_size = explode('(',$info->Type);
        if(count($type_size)>1) $type_size[1] = explode(',', preg_replace('/[\(\)\']/', '', $type_size[1]));
        $types[$info->Field] = $type_size;
    }
    return $types;
}
```

### getEditHTML
```php
public function getEditHTML($classes = 'mb-1'){

    $html = '';

    foreach ($this->editable as $column => $as){

        $this->attributes[$column] = isset($this->attributes[$column]) ? $this->attributes[$column] : ''; 

        switch($this->getColumnsType()[$column][0]){

            case 'varchar':

                if(str_contains($column, 'color')){

                    $html .= "<div class='input-group $classes'>";
                        $html .= "<div class='input-group-prepend'>";
                            $html .= "<div class='input-group-text p-1'>";
                                $html .= "<input type='color' id='$column' placeholder='$as' value='".$this->attributes[$column]."' class='border-0 p-0' onchange=\"document.querySelector('.modal.show [name=\'".\Str::singular($this->table)."[$column]\']').value = this.value;\">";
                            $html .= "</div>";
                        $html .= "</div>";
                        $html .= "<input type='text' name='".\Str::singular($this->table)."[$column]' placeholder='$as' value='".$this->attributes[$column]."' class='form-control' onkeyup=\"document.querySelector('.modal.show #$column').value = this.value;\">";
                    $html .= "</div>";
                }else{
                    $html .= "<input type='text' name='".\Str::singular($this->table)."[$column]' placeholder='$as' value='".$this->attributes[$column]."' class='form-control $classes'>";
                }
            break;
    
            case 'enum':
            case 'set':
                $html .= "<select name='".\Str::singular($this->table)."[$column]' class='form-control $classes'>";
                    foreach ($this->getColumnsType()[$column][1] as $option){

                        $html .= "<option ".($this->attributes[$column] == $option ? 'selected' : '')." value='$option'>".ucfirst($option).'</option>';
                    }
                $html .= '</select>';
            break;
    
            case 'int':
                $html .= "<input type='number' name='".\Str::singular($this->table)."[$column]' placeholder='$as' value='".$this->attributes[$column]."' class='form-control $classes'>";
            break;
    
            default:
                $html .= "<input type='text' name='".\Str::singular($this->table)."[$column]' placeholder='$as' value='".$this->attributes[$column]."' class='form-control $classes'>";
        }
    }

    return $html;
}
```

## CrudGeneratorCommand
- handle
- getStub
- model
- controller
- request
- views
