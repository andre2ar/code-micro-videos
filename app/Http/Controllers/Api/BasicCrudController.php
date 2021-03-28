<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();

    protected abstract function rulesStore(): array;

    protected abstract function rulesUpdate(): array;

    public function index()
    {
        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rulesStore());

        $object = $this->model()::create($validated);

        return $object->refresh();
    }

    public function show($id)
    {
        return $this->model()::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rulesUpdate());

        $object = $this->model()::findOrFail($id);

        $object->update($request->all());

        return $object;
    }

    public function destroy($id)
    {
        $object = $this->model()::findOrFail($id);

        $object->delete();

        return response()->noContent();
    }
}
