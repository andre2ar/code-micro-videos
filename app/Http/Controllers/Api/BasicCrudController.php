<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BasicCrudController extends Controller
{
    protected int $paginationSize = 15;

    protected abstract function model();

    protected abstract function rulesStore(): array;

    protected abstract function rulesUpdate(): array;

    protected abstract function resource();

    protected abstract function resourceCollection();

    public function index()
    {
        $data = !$this->paginationSize ? $this->model()::all() : $this->model()::paginate($this->paginationSize);

        $resource = $this->resource();
        $resourceCollection = $this->resourceCollection();

        $refResourceCollectionClass = new \ReflectionClass($this->resourceCollection());

        return $refResourceCollectionClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollection($data)
            : $resource::collection($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rulesStore());

        $object = $this->model()::create($validated);

        $object->refresh();

        $resource = $this->resource();

        return new $resource($object);
    }

    public function show($id)
    {
        $object = $this->model()::findOrFail($id);
        $resource = $this->resource();

        return new $resource($object);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rulesUpdate());

        $object = $this->model()::findOrFail($id);

        $object->update($request->all());

        $resource = $this->resource();

        return new $resource($object);
    }

    public function destroy($id)
    {
        $object = $this->model()::findOrFail($id);

        $object->delete();

        return response()->noContent();
    }
}
