//
//  Job.h
//  WinZip
//
//  Created by WinZip on 14-1-16.
//  Copyright (c) 2014年 Winzip. All rights reserved.
//

#ifndef __WinZip__ProcessorManager__
#define __WinZip__ProcessorManager__

#include <iostream>

#include <list>

#include "TaskItem.h"

class TaskProcessor;

class TaskManager {

public:
    static void AddPreparedTaskItem(TaskItem*);
    static void RemoveTaskItem(TaskItem*);
    static TaskItem* PickNextTask();
    static void StartNextTaskIfPossible();
    static void set_max_concurrency(int);
    
    static TaskItem* GetFirstStartChildTaskItem(Task*);
    
private:
    static int _max_concurrency;
    static std::list<TaskItem*> _waiting_tasks;
    static std::list<TaskItem*> _running_tasks;
};

#endif /* defined(__WinZip__ProcessorManager__) */
