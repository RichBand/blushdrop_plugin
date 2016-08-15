//
//  NLocalFile.h
//  FileStructure
//
//  Created by glority on 13-12-29.
//  Copyright (c) 2013年 glority. All rights reserved.
//

#ifndef __FileStructure__NLocalFile__
#define __FileStructure__NLocalFile__

#include <iostream>

#include "NFile.h"
#include "FileUploadToCloudTask.h"

class NLocalFolder;
class NLocalFile : public NFile {
public:
    NLocalFile(std::string path);
    FileUploadToCloudTask* UploadTo(std::shared_ptr<NCloudFolder> target, ProgressChanged onProgressChanged, TaskCompleted onTaskCompleted, TaskFailed onTaskFailed);
    enum FileType FileType() { return LocalFile; };
    
    void Delete(OnItemDeleted onItemDeleted);
    void Rename(std::string newName, OnItemRenamed onItemRenamed);
    
    std::shared_ptr<NLocalFile> get_shared_ptr();
};

#endif /* defined(__FileStructure__NLocalFile__) */
