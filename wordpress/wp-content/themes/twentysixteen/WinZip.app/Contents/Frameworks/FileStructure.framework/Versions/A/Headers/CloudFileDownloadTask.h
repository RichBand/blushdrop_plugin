//
//  CloudFileDownloadTask.h
//  FileStructure
//
//  Created by glority on 14-1-2.
//  Copyright (c) 2014年 glority. All rights reserved.
//

#ifndef __FileStructure__CloudFileDownloadTask__
#define __FileStructure__CloudFileDownloadTask__

#include <iostream>

#include "TaskItem.h"

@protocol CloudRequest;

class NCloudFile;
class NLocalFolder;

class CloudFileDownloadTask : public TaskItem {

public:
    CloudFileDownloadTask(std::shared_ptr<NCloudFile> source, std::shared_ptr<NLocalFolder> target, ProgressChanged onProgressChanged, TaskCompleted onTaskCompleted, TaskFailed onTaskFailed);
    ~CloudFileDownloadTask();

protected:
    void _mExecute(std::function<void()>);
    void _mCancel();

public:
    std::shared_ptr<NCloudFile> source() { return _source; };
    std::string OperatingItemName();
    std::shared_ptr<NLocalFolder> target() { return _target; };
    long TotalSize();

private:
    id<CloudRequest> _cloudDownloadRequest = nullptr;
    std::shared_ptr<NCloudFile> _source;
    std::shared_ptr<NLocalFolder> _target;
    
    void _StartDownload();
    
    void (^_downloadCompletionBlock)(NSError*) = nullptr;
    void (^_downloadInProgressBlock)(CGFloat) = nullptr;
    
    std::shared_ptr<NItem> _old;
    std::function<void()> _onExecuted;

};

#endif /* defined(__FileStructure__CloudFileDownloadTask__) */
