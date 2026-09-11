<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskController extends Controller
{
    public function listPublic(): JsonResource
    {
        $tasks = Task::paginate();

        return TaskResource::collection($tasks);
    }

    public function listPrivate(Request $request): JsonResource
    {
        $tasks = Task::where('created_by_user_id', $request->user()->id)->paginate();

        return TaskResource::collection($tasks);
    }

    public function show(Request $request): JsonResource
    {
        $task = Task::findOrFail($request->id);

        return new TaskResource($task);
    }

    public function create(TaskRequest $request): JsonResource
    {
        $request->validated();

        $task = Task::create([
            'name' => $request->name,
            'description' => $request->description,
            'completed' => $request->completed,
            'created_by_user_id' => $request->user()->id,
        ]);

        $task->save();

        return new TaskResource($task);
    }

    public function update(TaskRequest $request): JsonResource
    {
        $request->validated();

        $task = Task::findOrFail($request->id);

        $task->fill($request->all());

        $task->save();

        return new TaskResource($task);
    }

    public function delete(Request $request): JsonResponse
    {
        $task = Task::findOrFail($request->id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}
