//
//  NFile.h
//  FF
//
//  Created by glority on 13-12-23.
//  Copyright (c) 2013年 glority. All rights reserved.
//

#ifndef __FF__NFile__
#define __FF__NFile__

#include <string>
#include <list>

#include "NItem.h"

class NFile : public NItem {
public:
    NFile(std::string path) : NItem(path) {};
    long size() { return _size; };
    void set_size(long size)  { _size = size; };

    virtual std::shared_ptr<NItem> QueryInnerNode(FileFormat format);
protected:
    long _size = 0;
};
#endif /* defined(__FF__NFile__) */
