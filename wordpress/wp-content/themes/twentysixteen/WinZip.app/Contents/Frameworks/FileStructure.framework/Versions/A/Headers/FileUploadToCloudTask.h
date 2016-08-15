//
//  FileUploadToCloudTask.h
//  FileStructure
//
//  Created by glority on 14-1-6.
//  Copyright (c) 2014年 glority. All rights reserved.
//

#ifndef __FileStructure__FileUploadToCloudTask__
#define __FileStructure__FileUploadToCloudTask__

#include <string>

#include "TaskItem.h"

@protocol CloudRequest;
@protocol CloudFile;

class NLocalFile;
class NCloudFolder;

class FileUploadToCloudTask : public TaskItem {
    
public:
    FileUploadToCloudTask(std::shared_ptr<NLocalFile> source, std::shared_ptr<NCloudFolder> target, ProgressChanged onProgressChanged, TaskCompleted onTaskCompleted, TaskFailed onTaskFailed);
    ~FileUploadToCloudTask();

protected:
    void _mExecute(std::function<void()>);
    void _mCancel();

public:
    std::shared_ptr<NLocalFile> source() { return _source; };
    std::string OperatingItemName();
    std::shared_ptr<NCloudFolder> target() { return _target; };
    long TotalSize();

private:
    id<CloudRequest> _cloudUploadRequest = nullptr;
    std::shared_ptr<NLocalFile> _source;
    std::shared_ptr<NCloudFolder> _target;
    
    void _StartUpload();
    
    void (^_uploadCompletionBlock)(id<CloudFile>, NSError*) = nullptr;
    void (^_uploadInProgressBlock)(CGFloat) = nullptr;
    
    BOOL cancelShouldDoNothing;

    std::shared_ptr<NItem> _old;
    std::function<void()> _onExecuted;
};
#endif /* defined(__FileStructure__FileUploadToCloudTask__) */
