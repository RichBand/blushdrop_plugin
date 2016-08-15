//
//  Task.h
//  WinZip
//
//  Created by WinZip on 14-1-15.
//  Copyright (c) 2014年 Winzip. All rights reserved.
//

#ifndef WinZip_Task_h
#define WinZip_Task_h

#include <iostream>
#include <vector>
#include "TaskErrorCode.h"

enum TaskAlertResponse {
    REPLACE,
    OVERWRITE,
    SKIP,
    RETRY,
    CANCEL,
};

class Task;
class TaskItem;
class TaskGroup;

typedef std::function<void(TaskItem*, float progress)> ProgressChanged;
typedef std::function<void(Task*)> TaskCompleted;
typedef std::function<TaskAlertResponse(Task*, TaskErrorCode, std::vector<std::string>* details)> TaskFailed;

class Task {
public:
    Task(ProgressChanged onProgressChanged, TaskCompleted onTaskCompleted, TaskFailed onTaskFailed): _on_progress_changed(onProgressChanged), _on_task_completed(onTaskCompleted), _on_task_failed(onTaskFailed) {};
    virtual ~Task() {};
    
    virtual int TotalItemCount() = 0;
    virtual long TotalSize() = 0;
    virtual long CompletedSize() = 0;
    virtual float Progress() = 0;

    virtual void PrepareToStart() = 0;
    virtual void Cancel() = 0;

public:
    virtual void MarkCompleted() = 0;
    
    bool Cancelled() { return _cancelled; };
    bool Completed() { return _completed; };
    virtual std::string OperatingItemName() {return "";};

public:
    TaskGroup* parent() { return _parent; };
    void set_parent(TaskGroup* parent) { _parent = parent; };

protected:
    TaskGroup* _parent = nullptr;

    ProgressChanged _on_progress_changed;
    TaskCompleted _on_task_completed;
    TaskFailed _on_task_failed;

    bool _cancelled = false;
    bool _completed = false;
    bool _running = false;
};

#endif
