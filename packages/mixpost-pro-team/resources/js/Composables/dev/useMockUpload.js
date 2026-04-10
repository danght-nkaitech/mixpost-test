// DEV ONLY: This file should not be imported in production
import { nanoid } from 'nanoid'

export const createMockUpload = uploadJobs => {
  const mockFiles = [
    { name: 'large-video-file.mp4' },
    { name: 'image-photo.jpg' },
    { name: 'another-video.mov' }
  ]

  mockFiles.forEach((file, index) => {
    const jobId = nanoid()
    uploadJobs.value.push({
      id: jobId,
      file,
      progress: index * 30,
      status: index === 0 ? 'uploading' : 'pending',
      error: null,
      uploadInstance: null
    })
  })

  // Simulate progress
  const interval = setInterval(() => {
    const activeJob = uploadJobs.value.find(job => job.status === 'uploading')
    if (activeJob) {
      activeJob.progress = Math.min(activeJob.progress + 5, 100)
      if (activeJob.progress >= 100) {
        activeJob.status = 'complete'
        const nextJob = uploadJobs.value.find(job => job.status === 'pending')
        if (nextJob) {
          nextJob.status = 'uploading'
        } else {
          clearInterval(interval)
        }
      }
    } else {
      clearInterval(interval)
    }
  }, 200)

  return interval
}
