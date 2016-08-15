//
//  NCloudFile.h
//  FileStructure
//
//  Created by glority on 12/29/13.
//  Copyright (c) 2013 glority. All rights reserved.
//

#ifndef FileStructure_NCloudFile_h
#define FileStructure_NCloudFile_h

#include "NFile.h"

#include "CloudFileDownloadTask.h"

@protocol CloudService;
@protocol CloudFile;

class NLocalFolder;
class NCloudFolder;

typedef std::function<void(std::string link)> ShareLinkLoaded;

class NCloudFile : public NFile {
public:
    NCloudFile(std::string path, id entry, id service);
    ~NCloudFile();
    CloudFileDownloadTask* DownloadTo(std::shared_ptr<NLocalFolder> target, ProgressChanged onProgressChanged, TaskCompleted onTaskCompleted, TaskFailed onTaskFailed);
    enum FileType FileType() { return CloudFile; };
    id<CloudService> CloudService() { return _cloud_service; };
    id<CloudFile> cloud_file_entry() { return _cloud_file_entry; };
    
    std::shared_ptr<NCloudFile> get_shared_ptr();
public:
    std::string share_link() { return _share_link; };
    void LoadShareLink(ShareLinkLoaded);
    
    void Delete(OnItemDeleted onItemDeleted);
    void Rename(std::string name, OnItemRenamed onItemRenamed);
protected:
    id<CloudFile> _cloud_file_entry;
    id<CloudService> _cloud_service;
    
    std::string _share_link;
};

#endif
